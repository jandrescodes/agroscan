# PROMPTS.md — AgroScan

> Plantillas de prompts para el equipo. Adapta los bloques `[Tarea]` y `[Contexto]`
> a lo que necesites en cada sesión. `CLAUDE.md` y `AGENT.md` deben estar disponibles
> como contexto base.

---

## Cómo usar este archivo

| Eje                   | Pregunta          | Para qué sirve                                 |
|-----------------------|-------------------|------------------------------------------------|
| **Rol**               | ¿Quién eres?      | Define el nivel y especialidad del agente      |
| **Contexto**          | ¿Dónde estamos?   | El proyecto, stack y módulo activo             |
| **Tarea exacta**      | ¿Qué necesitas?   | Concreto y específico — nunca genérico         |
| **Restricciones**     | ¿Qué límites hay? | Convenciones del proyecto que no se rompen     |
| **Formato de salida** | ¿Cómo lo quieres? | Estructura del output esperado                 |

**Reglas de uso:**

- **Siempre carga CLAUDE.md y AGENT.md** al inicio de la sesión.
- **Un prompt por subtarea.** Pedir "todo el módulo" produce código genérico.
- **El spec antes que el código.** Define qué debe hacer antes de pedir que lo implemente.
- **Si el output no encaja**, ajusta `[Restricciones]` y repite — no corrijas manualmente primero.

---

## Plantilla base

```
[Rol]
Actúa como desarrollador Laravel Senior especializado en integraciones
con APIs de IA y arquitectura MVC con Eloquent.

[Contexto]
Proyecto: AgroScan — Laravel 12, MySQL, Tailwind CSS v4, Gemini Vision API, Open-Meteo.
Módulo activo: _______________

[Tarea]
_______________

[Restricciones]
- Seguir convenciones de Laravel 12 (Controllers, Models, Services, FormRequests)
- Services inyectados vía constructor — sin app() ni resolve() en controladores
- GeminiService lanza \RuntimeException en fallo; el controlador la atrapa y redirige
- WeatherService devuelve null en fallo — nunca bloquea el flujo principal
- Validación de imagen: mimes:jpg,jpeg,png,webp|max:5120 en FormRequest
- Blade con {{ }} para todo output — sin {!! !!} salvo HTML de confianza interna
- Colores de riesgo: bajo=verde, medio=amarillo, alto=rojo
- Sin jQuery — vanilla JS o Alpine.js
- No exponer GEMINI_API_KEY en logs, respuestas HTTP ni código

[Formato de salida]
_______________
```

---

## Plantilla 1 — Implementar un feature nuevo

Usar cuando: agregar una funcionalidad nueva al proyecto.

```
[Rol]
Actúa como desarrollador Laravel Senior especializado en integraciones
con APIs de IA y buenas prácticas de Laravel 12.

[Contexto]
Proyecto: AgroScan — Laravel 12, MySQL, Tailwind CSS v4, Blade, Gemini Vision API, Open-Meteo.
Módulo activo: [nombre — ej: diagnosis, history, reports]

Archivos relevantes:
- app/Http/Controllers/[Modulo]Controller.php
- app/Models/[Modulo].php
- app/Services/GeminiService.php  (si toca análisis de imagen)
- app/Services/WeatherService.php (si toca clima)
- resources/views/[modulo]/[vista].blade.php
- routes/web.php

[Tarea]
Implementar [nombre exacto del feature].

Descripción: [criterios de aceptación]

[Restricciones]
- Seguir el patrón Controller → Service → Model de DiagnosisController como referencia
- FormRequest para validación — nunca validate() inline en el controlador
- Imagen almacenada en storage/app/public/diagnoses/ vía Storage::disk('public')
- GeminiService::analyze() recibe la ruta local de la imagen y el tipo de cultivo (`crop`)
- Respuesta de Gemini siempre validada contra el contrato JSON definido en AGENT.md
- WeatherService::getConditions() recibe lat/lng; retorna null si falla
- Blade components para badge de `risk_level` y card de resultado
- Strings en español; sin hardcodear coordenadas — parametrizar ubicación

[Formato de salida]
Devuelve en este orden:
1. Lista de archivos que se crean o modifican
2. Rutas a agregar en routes/web.php
3. Migración SQL si hay cambios en BD
4. Código de cada archivo
5. Checklist de testing manual (flujos exitosos + edge cases)
```

---

## Plantilla 2 — Debuggear un error

Usar cuando: algo no funciona y no está claro por qué.

```
[Rol]
Actúa como desarrollador Laravel Senior especializado en debugging
de integraciones HTTP y APIs externas.

[Contexto]
Proyecto: AgroScan — Laravel 12, Gemini Vision API, Open-Meteo.
Archivo donde ocurre el error: [ruta completa]
Método/función afectada: [nombre]

[Tarea]
Tengo este error:
[pega el mensaje exacto o el comportamiento inesperado]

Código actual:
[pega el bloque relevante — no todo el archivo]

Lo que debería hacer:
[comportamiento esperado]

Lo que intenté:
[lo que ya probaste]

[Restricciones]
- No cambiar la arquitectura — solo corregir el problema específico
- Si el fix toca más de un archivo, indicarlo antes de proponer código
- No introducir dependencias nuevas para resolver el bug

[Formato de salida]
1. Diagnóstico: causa raíz en 2-3 líneas
2. Fix: código corregido con comentario explicando el cambio
3. Por qué pasó: explicación breve para no repetirlo
```

---

## Plantilla 3 — Ajustar el prompt de Gemini

Usar cuando: el diagnóstico devuelve plagas incorrectas, confianza baja, o JSON malformado.

```
[Rol]
Actúa como prompt engineer especializado en Gemini Vision API
y en agronomía de cultivos tropicales.

[Contexto]
Proyecto: AgroScan — diagnóstico de plagas en Santa Cruz, Bolivia.
Plagas objetivo: gusano cogollero, nematodos, bacteriosis, monilia, roya.
Cultivos principales: maíz, soya, cacao, arroz, trigo, sorgo, hortalizas.
Archivo del prompt: app/Services/GeminiService.php (método buildPrompt o similar).

[Tarea]
El prompt actual produce estos problemas:
[describe: JSON inválido / plaga equivocada / confianza siempre baja / etc.]

Prompt actual:
[pega el prompt actual]

[Restricciones]
- La respuesta de Gemini DEBE ser solo JSON válido — sin texto adicional, sin markdown
- El JSON debe cumplir exactamente el contrato definido en AGENT.md
- `risk_level` solo puede ser: `"low"`, `"medium"` o `"high"`
- `confidence` entre 0.0 y 1.0
- Mencionar explícitamente el contexto geográfico de Santa Cruz, Bolivia
- Mencionar las 5 plagas objetivo para que Gemini las priorice
- Si no hay plaga visible: `has_problem=false`, `pest_name=null`, resto `null`

[Formato de salida]
1. Diagnóstico del problema con el prompt actual
2. Prompt corregido completo (listo para pegar en el código)
3. Ejemplos de respuesta JSON esperada para 2 casos: con plaga y sin plaga
```

---

## Plantilla 4 — Code review antes del merge

Usar cuando: antes de hacer merge de una rama o el código funciona pero hay dudas.

```
[Rol]
Actúa como Tech Lead Laravel con experiencia en seguridad web,
integraciones con APIs de IA y code review.

[Contexto]
Proyecto: AgroScan — Laravel 12, Gemini Vision API, Open-Meteo.
Rama revisada: feature/[nombre]
Cambio implementado: [descripción breve]

[Tarea]
Revisa el siguiente código antes del merge:

[pega el código o el diff]

[Restricciones]
Evalúa específicamente:
- Seguridad: API key no expuesta, validación de imagen, XSS (Blade {{ }}), CSRF token en forms
- Manejo de errores: GeminiService lanza excepción, controlador la atrapa correctamente
- Flujo degradado: WeatherService falla → diagnóstico continúa con campos climáticos null
- Contrato JSON: respuesta de Gemini validada antes de persistir
- Storage: imagen guardada en disco correcto, path relativo en BD
- Casos edge: imagen corrupta, JSON parcial de Gemini, timeout de API

[Formato de salida]
OK  - Lo que está bien (al menos 2 cosas)
OBS - Observaciones (mejoras no críticas, con sugerencia)
FIX - Problemas a corregir antes del merge (con código corregido)
```

---

## Plantilla 5 — Nuevo módulo completo (spec-first)

Usar cuando: se va a implementar un módulo nuevo de principio a fin.

```
[Rol]
Actúa como desarrollador Laravel Senior especializado en arquitectura
MVC, diseño de base de datos y buenas prácticas de Laravel 12.

[Contexto]
Proyecto: AgroScan — Laravel 12, MySQL, Tailwind CSS v4, Gemini Vision API, Open-Meteo.
Módulo nuevo: [nombre]

BD existente relevante:
- diagnoses (id, image_path, crop, location, has_problem, pest_name,
  risk_level, description, immediate_action, preventive_action, confidence,
  temperatura, humedad, condicion_clima, created_at, updated_at)

[Tarea]
Implementar el módulo [nombre] con las siguientes funcionalidades:
[lista de operaciones]

Criterios de aceptación:
[pega los criterios]

[Restricciones]
- FormRequest para validación — sin validate() inline
- Services inyectados vía constructor
- Blade con {{ }} para todo output
- Tailwind CSS v4 — sin CSS custom salvo necesidad justificada
- Colores semánticos: bajo=verde, medio=amarillo, alto=rojo
- Sin jQuery — vanilla JS o Alpine.js
- Strings en español

[Formato de salida]
Devuelve en este orden:
1. Migración (si hay cambios en BD)
2. Rutas en routes/web.php
3. FormRequest
4. Model
5. Service (si aplica)
6. Controller
7. Vistas Blade
8. Checklist de testing manual
```

---

*Última actualización: Sprint 1 — scaffold inicial*
*Mantener sincronizado con CLAUDE.md y AGENT.md al iniciar cada sprint.*
