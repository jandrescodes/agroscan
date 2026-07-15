<p align="center">
  <h1 align="center">AgroScan 🌱</h1>
  <p align="center"><em>Diagnóstico inteligente de plagas para pequeños productores de Santa Cruz, Bolivia.</em></p>
</p>

<p align="center">
  Desarrollado para el hackathon <strong>Build With AI 2026</strong> organizado por <strong>GDG Santa Cruz</strong> y la <strong>Universidad Católica Boliviana (UCB)</strong>.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Gemini-2.5%20Flash-4285F4?logo=google&logoColor=white" alt="Gemini 2.5 Flash">
  <img src="https://img.shields.io/badge/Tailwind-v4-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS v4">
  <img src="https://img.shields.io/badge/License-MIT-22c55e" alt="MIT License">
  <img src="https://img.shields.io/badge/Build%20With%20AI-2026-f97316" alt="Build With AI 2026">
</p>

---

AgroScan es una app web donde el agricultor sube una foto de su cultivo y recibe en segundos un diagnóstico de plagas impulsado por IA (Gemini 2.5 Flash), junto con acciones inmediatas y preventivas adaptadas al clima actual de Santa Cruz, Bolivia.

---

## Equipo

| Nombre                    | GitHub                                     |
| ------------------------- | ------------------------------------------ |
| José Andrés Meneces López | [@jandrescodes](https://github.com/jandrescodes) |
| José María Orozco Sossa   | [@Jhos3ph](https://github.com/Jhos3ph)     |

Contacto: jandrespb4@gmail.com

---

## Capturas de pantalla

### Formulario de diagnóstico

Selección de cultivo, carga de imagen con vista previa y ubicación opcional.

![Formulario de diagnóstico](docs/screenshots/create.png)

---

### Resultado del diagnóstico

Plaga detectada, nivel de riesgo, acciones recomendadas y condiciones climáticas actuales.

![Resultado del diagnóstico](docs/screenshots/show.png)

---

### Historial de diagnósticos

Lista paginada de todos los análisis realizados con badge de riesgo y fecha.

![Historial de diagnósticos](docs/screenshots/index.png)

---

## Cómo funciona

1. El agricultor selecciona el cultivo, sube una foto y opcionalmente indica su municipio.
2. **WeatherService** consulta Open-Meteo con las coordenadas del municipio y obtiene temperatura, humedad y condición del cielo en tiempo real.
3. **GeminiService** envía la imagen + el contexto climático a Gemini Vision (Vertex AI) y recibe un JSON con plaga detectada, nivel de riesgo y acciones adaptadas al clima actual.
4. El resultado se guarda en la base de datos y se muestra al agricultor junto con un chat de consultas de seguimiento.

---

## Stack tecnológico

| Tecnología        | Versión          | Para qué sirve                         |
| ----------------- | ---------------- | -------------------------------------- |
| PHP               | 8.2+             | Lenguaje del backend                   |
| Laravel           | 12.x             | Framework principal (rutas, BD, vistas)|
| MariaDB           | 10.x+            | Base de datos (dev y producción)       |
| SQLite            | —                | Base de datos solo en tests            |
| Gemini Vision API | gemini-2.5-flash | Análisis de imagen con IA (Vertex AI)  |
| Open-Meteo API    | —                | Clima en tiempo real (sin clave de API)|
| Alpine.js         | 3.15.x           | Interactividad mínima en el frontend   |
| Tailwind CSS      | 4.x              | Estilos                                |
| Vite              | 7.x              | Empaquetador de assets JS/CSS          |

---

## Arquitectura

```
agroscan/
├── app/
│   ├── Http/Controllers/
│   │   └── DiagnosisController.php   ← maneja create, store, show, index, consulta
│   ├── Http/Requests/
│   │   └── DiagnosisFormRequest.php  ← valida el formulario antes de procesar
│   ├── Models/
│   │   └── Diagnosis.php             ← representa un diagnóstico en la BD
│   └── Services/
│       ├── GeminiService.php         ← llama a Vertex AI (Gemini Vision)
│       └── WeatherService.php        ← llama a Open-Meteo para el clima
├── config/
│   └── gemini.php                    ← project_id, model, location, timeout
├── database/migrations/
│   └── ..._create_diagnoses_table.php
├── resources/views/diagnosis/
│   ├── create.blade.php              ← formulario de carga de imagen
│   ├── show.blade.php                ← resultado + chat de consultas
│   └── index.blade.php               ← historial paginado
└── routes/web.php                    ← todas las rutas de la app
```

> **Nota para nuevos en Laravel:** las "vistas" son los archivos `.blade.php` (lo que el usuario ve), los "controladores" reciben las peticiones HTTP y los "services" contienen la lógica de negocio (llamadas a APIs externas).

---

## Requisitos previos

Antes de correr el proyecto necesitás tener instalado:

- **PHP 8.2+** — el lenguaje del backend. Verificá con `php -v`.
- **Composer** — el gestor de paquetes de PHP, como npm pero para PHP. Verificá con `composer -V`.
- **Node.js 18+ y npm** — para compilar el CSS y JS. Verificá con `node -v`.
- **MariaDB o MySQL** — la base de datos. Podés usar XAMPP o Laragon en Windows/Mac.
- **Cuenta de Google Cloud** con Vertex AI habilitado y Application Default Credentials (ADC) configuradas — ver sección siguiente.

---

## Configurar Google Cloud (ADC)

AgroScan usa **Application Default Credentials (ADC)** para autenticarse con Vertex AI, sin necesidad de manejar archivos de clave manualmente.

**Opción A — Con gcloud CLI (recomendado para desarrollo local):**

```bash
# 1. Instalar gcloud CLI: https://cloud.google.com/sdk/docs/install
# 2. Iniciar sesión
gcloud auth application-default login
# 3. Seleccionar el proyecto
gcloud config set project TU_PROJECT_ID
```

**Opción B — Con archivo de service account (para servidores o CI):**

```bash
# Descargá el JSON de la service account desde Google Cloud Console
# y ponés la ruta en el .env:
GOOGLE_APPLICATION_CREDENTIALS=/ruta/absoluta/a/credentials.json
```

> En ambos casos, asegurate de que el proyecto tenga **Vertex AI API** habilitada en Google Cloud Console.

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/jandrescodes/agroscan.git
cd agroscan

# 2. Configurar las variables de entorno
#    (el siguiente paso lo hace automáticamente, pero si querés hacerlo a mano:
#     cp .env.example .env  y completar los valores de la sección siguiente)

# 3. Setup completo con un solo comando
#    Instala dependencias PHP y JS, copia .env, genera la clave de app y corre migraciones
composer run setup
```

Si el comando de setup falla, asegurate de haber configurado la base de datos en `.env` antes de correrlo (ver sección siguiente).

---

## Variables de entorno

Abrí el archivo `.env` (se crea solo con `composer run setup`) y completá estos valores:

```env
# ── Base de datos ──────────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agroscan      # Nombre de la BD que creaste en MySQL/MariaDB
DB_USERNAME=root           # Tu usuario de MySQL
DB_PASSWORD=               # Tu contraseña (vacía si usás XAMPP por defecto)

# ── Google Cloud / Vertex AI ───────────────────────────────────────────────────
GOOGLE_CLOUD_PROJECT=tu-project-id          # El ID de tu proyecto en Google Cloud
# Solo si usás Opción B (service account):
# GOOGLE_APPLICATION_CREDENTIALS=/ruta/a/credentials.json

# ── Gemini — opcionales, los defaults ya funcionan ────────────────────────────
GEMINI_MODEL=gemini-2.5-flash
GEMINI_LOCATION=us-central1
GEMINI_TIMEOUT=30
```

> **Nunca** subas el archivo `.env` al repositorio — ya está en `.gitignore`. Tampoco subas archivos de credenciales de Google Cloud.

---

## Levantar el servidor de desarrollo

```bash
composer run dev
```

Este comando levanta **todo a la vez**:
- Servidor Laravel en `http://localhost:8000`
- Procesador de colas (jobs en segundo plano)
- Visor de logs en tiempo real
- Vite (recarga automática al guardar cambios en CSS/JS)

Dejalo corriendo en una terminal y abrí `http://localhost:8000` en el navegador.

---

## Comandos útiles

```bash
# Levantar todo el entorno de desarrollo
composer run dev

# Correr los tests
composer run test

# Correr un test específico
php artisan test --filter NombreDelTest

# Ver los logs en tiempo real (sin composer run dev)
php artisan pail

# Probar código PHP interactivo (como una consola)
php artisan tinker

# Crear el enlace para ver imágenes subidas en el navegador (solo la primera vez)
php artisan storage:link

# Correr las migraciones (cuando hay cambios en la base de datos)
php artisan migrate
```

---

## Ejecución de tests

```bash
composer run test
```

Los tests usan **SQLite en memoria** — no necesitás tener MariaDB corriendo para ejecutarlos. SQLite se crea y destruye automáticamente en cada ejecución.

---

## Plagas objetivo (Santa Cruz, Bolivia)

| Plaga            | Cultivos típicos  |
| ---------------- | ----------------- |
| Gusano cogollero | Maíz, sorgo       |
| Nematodos        | Soya, hortalizas  |
| Bacteriosis      | Arroz, tomate     |
| Monilia          | Cacao             |
| Roya             | Soya, café, trigo |

---

## Licencia

MIT

---

<p align="center">
  Hecho con ❤️ en Santa Cruz, Bolivia · <strong>Build With AI 2026</strong> · GDG Santa Cruz · UCB
</p>
