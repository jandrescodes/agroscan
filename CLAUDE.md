# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# First-time setup
composer run setup

# Start all dev services (Laravel server + queue + log watcher + Vite)
composer run dev

# Run all tests
composer run test

# Run a single test file or filter
php artisan test --filter TestClassName
php artisan test tests/Feature/ExampleTest.php

# Code style (Laravel Pint)
./vendor/bin/pint

# Interactive REPL
php artisan tinker

# Run migrations
php artisan migrate

# Tail logs
php artisan pail
```

## Stack

- **Laravel 12**, PHP 8.2+
- **Database:** MariaDB (producción / dev local con XAMPP) — SQLite solo para tests
- **Frontend:** Tailwind CSS v4 via `@tailwindcss/vite`, Blade templates, Vite 7
- **IA:** Gemini 2.5 Flash via Gemini Vision API (`GEMINI_API_KEY`, `GEMINI_MODEL` en `.env`)
- **Clima:** Open-Meteo API (sin clave — HTTP público)
- **Queue/Cache/Session:** Database driver (local dev)
- **Tests:** PHPUnit 11 (`tests/Feature/`, `tests/Unit/`)

## Architecture

Standard Laravel MVC.

```
app/
├── Http/Controllers/
│   └── DiagnosisController.php   ← create, store, show, index, consulta
├── Http/Requests/
│   └── DiagnosisFormRequest.php
├── Models/
│   └── Diagnosis.php
├── Services/
│   ├── GeminiService.php           ← Gemini Vision API; analyze() + consultarSobreDiagnostico()
│   └── WeatherService.php          ← Open-Meteo; resolución de coordenadas por municipio SCZ
config/
└── gemini.php                      ← api_key, model, endpoint, timeout
database/migrations/
└── ..._create_diagnoses_table.php
resources/views/
└── diagnosis/
    ├── create.blade.php            ← formulario de carga de imagen
    ├── show.blade.php              ← resultado del diagnóstico + chat de consultas
    └── index.blade.php             ← historial paginado
routes/
└── web.php
```

## Domain — Plagas objetivo (Santa Cruz, Bolivia)

| Plaga            | Cultivo típico    |
| ---------------- | ----------------- |
| Gusano cogollero | Maíz, sorgo       |
| Nematodos        | Soya, hortalizas  |
| Bacteriosis      | Arroz, tomate     |
| Monilia          | Cacao             |
| Roya             | Soya, café, trigo |

## Gemini response contract

El prompt instruye a Gemini a devolver **solo** JSON válido con esta forma:

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

`risk_level` ∈ `["low", "medium", "high"]`. Si no hay problema, `pest_name` es `null`.

## Conventions

- Services are injected via constructor DI — no `app()` calls inside controllers.
- `GeminiService` throws `\RuntimeException` on API errors; the controller catches and redirects with an error flash.
- `WeatherService` returns `null` on failure — weather is enrichment, not required.
- `WeatherService::getConditions(?string $location)` resuelve coordenadas por municipio de SCZ (25 zonas conocidas); si no hay match usa `-17.7833, -63.1821` (SCZ ciudad).
- El clima se fetcha **antes** de llamar a Gemini para incluirlo en el prompt de análisis.
- `GeminiService::consultarSobreDiagnostico()` maneja el chat de consultas — respuesta efímera, no se persiste.
- Images are stored in `storage/app/public/diagnoses/` via `Storage::disk('public')`.
- All user-facing strings are in Spanish.
- No JS frameworks — plain Blade + Alpine.js if lightweight interactivity is needed.
- Timezone: `America/La_Paz` (UTC-4).

## Notes

- `AGENT.md` and `PROMPTS.md` contain project context and prompt templates for AI-assisted development.
- Never commit `GEMINI_API_KEY` — it lives only in `.env` (gitignored).
