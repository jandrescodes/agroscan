# AGENT.md — AgroScan

> System prompt persistente para agentes de IA y sesiones de desarrollo asistido.
> Compatible con: Claude Code · Cursor (.cursorrules) · Claude.ai (pegar al inicio)

---

## Proyecto

**AgroScan** — App Laravel 12 para diagnóstico de plagas en cultivos de Santa Cruz, Bolivia.
El agricultor sube una foto del cultivo; la app consulta Gemini Vision API y Open-Meteo para
devolver un diagnóstico estructurado con acciones inmediatas y preventivas.

**Estado actual:** Sprint 2 — MVP funcional con diagnóstico IA, agente de consultas, historial paginado y clima contextualizado por municipio.

---

## Stack Tecnológico

- **Backend:** Laravel 12, PHP 8.2+, Eloquent ORM
- **Frontend:** Tailwind CSS v4, Blade templates, Vite 7
- **Base de datos:** MariaDB (dev con XAMPP / prod) — SQLite solo en tests
- **IA:** Gemini 2.5 Flash (Gemini Vision API) — clave en `GEMINI_API_KEY`
- **Clima:** Open-Meteo API (pública, sin clave)
- **Control de versiones:** Git + GitHub

---

## Arquitectura del Proyecto

```
agroscan/
├── app/
│   ├── Http/Controllers/
│   │   └── DiagnosisController.php   ← create, store, show, index, consulta
│   ├── Http/Requests/
│   │   └── DiagnosisFormRequest.php
│   ├── Models/
│   │   └── Diagnosis.php
│   └── Services/
│       ├── GeminiService.php         ← Gemini Vision API; analyze() + consultarSobreDiagnostico()
│       └── WeatherService.php        ← Open-Meteo; coordenadas por municipio SCZ
├── config/
│   └── gemini.php
├── database/
│   └── migrations/
│       └── ..._create_diagnoses_table.php
├── resources/views/
│   └── diagnosis/
│       ├── create.blade.php          ← formulario con drag-drop y loading overlay
│       ├── show.blade.php            ← resultado + chat de consultas (Alpine)
│       └── index.blade.php           ← historial paginado
└── routes/
    └── web.php
```

---

## Base de Datos

```sql
diagnoses (
    id                  BIGINT PK,
    image_path          VARCHAR,        -- ruta en storage/app/public/diagnoses/
    crop                VARCHAR,        -- maíz, soya, cacao, etc.
    location            VARCHAR,        -- ciudad/municipio referencial
    has_problem         BOOLEAN,
    pest_name           VARCHAR NULL,
    risk_level          ENUM('low','medium','high') NULL,
    description         TEXT NULL,
    immediate_action    TEXT NULL,
    preventive_action   TEXT NULL,
    confidence          DECIMAL(4,3) NULL,
    temperature         DECIMAL(5,2) NULL,  -- °C desde Open-Meteo
    humidity            DECIMAL(5,2) NULL,  -- % desde Open-Meteo
    weather_condition   VARCHAR NULL,
    created_at, updated_at
)
```

---

## Plagas Objetivo (Santa Cruz, Bolivia)

| Plaga            | Cultivo típico    | Nivel típico |
| ---------------- | ----------------- | ------------ |
| Gusano cogollero | Maíz, sorgo       | alto         |
| Nematodos        | Soya, hortalizas  | medio-alto   |
| Bacteriosis      | Arroz, tomate     | medio        |
| Monilia          | Cacao             | alto         |
| Roya             | Soya, café, trigo | medio-alto   |

---

## Contrato de Respuesta Gemini

El prompt siempre instruye a Gemini a responder **solo** con JSON válido:

```json
{
    "has_problem": true,
    "pest_name": "Gusano cogollero",
    "risk_level": "high",
    "description": "...",
    "immediate_action": "...",
    "preventive_action": "...",
    "confidence": 0.92
}
```

`risk_level` ∈ `["low", "medium", "high"]`. Si no se detecta plaga: `has_problem: false`, `pest_name: null`, resto `null`.

---

## Convenciones de Código

### PHP — Controladores

- Un controlador por módulo: `DiagnosisController`, etc.
- Métodos: `create()` (formulario), `store()` (procesa imagen), `show()` (resultado), `index()` (historial paginado), `consulta()` (chat JSON efímero).
- Services inyectados vía constructor — nunca `app()` ni `resolve()` en controladores.
- El controlador atrapa `\RuntimeException` de `GeminiService` y redirige con `->with('error', ...)`.
- En `store()`: el clima se fetcha **antes** de llamar a Gemini para enriquecer el prompt.

### PHP — Services

- `GeminiService::analyze(string $imagePath, string $crop, ?array $weather = null): array` — devuelve el array decodificado del JSON de Gemini. El clima se incluye en el prompt si está disponible. Lanza `\RuntimeException` en fallo de API o JSON inválido.
- `GeminiService::consultarSobreDiagnostico(array $diagnostico, string $pregunta): string` — responde preguntas de seguimiento en texto libre; reutiliza ADC + cURL de `analyze()` sin `inline_data`. Respuesta efímera, no se persiste.
- `WeatherService::getConditions(?string $location = null): ?array` — devuelve array con `temperature`, `humidity`, `weather_condition`, `resolved_location` o `null` si falla. Resuelve coordenadas específicas para 25 municipios/zonas del departamento de Santa Cruz.

### PHP — Modelos

- Eloquent estándar. Fillable explícito en cada modelo.
- Casting para `has_problem` (boolean) y `confidence` (float).
- Sin borrado físico en tablas de negocio — usar `SoftDeletes` si se necesita borrado.

### PHP — Seguridad

- Validar imagen: `mimes:jpg,jpeg,png,webp|max:5120` en el `FormRequest`.
- Nunca exponer `GEMINI_API_KEY` en logs ni respuestas.
- Sanitizar todo output en Blade con `{{ }}` (escapa automáticamente).

### Frontend

- Tailwind CSS v4 — clases utilitarias, sin CSS custom salvo necesidad justificada.
- Blade components para partes reutilizables (badge de nivel de riesgo, card de resultado).
- Colores semánticos de nivel de riesgo: `bajo`=verde, `medio`=amarillo, `alto`=rojo.
- Sin jQuery — vanilla JS o Alpine.js para interactividad mínima.

---

## Flujo de Trabajo Git

```
main    ← Producción
dev     ← Integración (base para features)
feature/descripcion  ← Una rama por feature
```

**Mensajes de commit (Conventional Commits en español):**

```
feat(diagnosis): implementar análisis de imagen con Gemini Vision
fix(weather): manejar timeout de Open-Meteo sin romper el flujo
refactor(gemini): extraer construcción del prompt a método privado
```

---

## Prohibiciones Explícitas

- **NO** exponer la API key de Gemini en código, logs ni respuestas HTTP.
- **NO** hacer llamadas HTTP síncronas largas sin timeout configurado en el service.
- **NO** guardar imágenes fuera de `storage/app/public/diagnoses/`.
- **NO** devolver el JSON crudo de Gemini al frontend — siempre pasar por el contrato.
- **NO** introducir paquetes Composer nuevos sin revisar que no exista solución nativa en Laravel 12.
- **NO** usar `dd()` / `dump()` en código que llegue a producción.

---

## Contexto para el Agente

Al generar código para este proyecto:

1. **Seguir el contrato JSON de Gemini** — cualquier campo fuera del schema debe ignorarse o loguearse como warning.
2. **Weather es enriquecimiento** — si `WeatherService` falla, el diagnóstico continúa con campos climáticos en `null`.
3. **El prompt de Gemini debe mencionar explícitamente** las 5 plagas objetivo y el contexto geográfico de Santa Cruz, Bolivia.
4. **Validar en ambos lados** — `FormRequest` en backend, feedback visual inmediato en frontend.
5. **Pensar en el stack completo** — cada feature toca: ruta, controlador, service, modelo, migración y vista.
6. **No asumir que existe código** — si no está listado en la arquitectura, verificar antes de referenciarlo.

---

_Última actualización: Sprint 2 — MVP completo_
_Mantener este archivo actualizado al iniciar cada nuevo sprint._
