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
- **Database:** MySQL (producción / dev local con Docker) — SQLite solo para tests
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
│   └── DiagnosticoController.php   ← sube imagen, llama servicios, persiste resultado
├── Models/
│   └── Diagnostico.php
├── Services/
│   ├── GeminiService.php           ← llama Gemini Vision API, devuelve array estructurado
│   └── WeatherService.php          ← llama Open-Meteo, devuelve condiciones actuales
config/
└── gemini.php                      ← api_key, model, endpoint, timeout
database/migrations/
└── ..._create_diagnosticos_table.php
resources/views/
└── diagnostico/
    ├── crear.blade.php             ← formulario de carga de imagen
    └── show.blade.php              ← resultado del diagnóstico
routes/
└── web.php
```

## Domain — Plagas objetivo (Santa Cruz, Bolivia)

| Plaga              | Cultivo típico         |
|--------------------|------------------------|
| Gusano cogollero   | Maíz, sorgo            |
| Nematodos          | Soya, hortalizas       |
| Bacteriosis        | Arroz, tomate          |
| Monilia            | Cacao                  |
| Roya               | Soya, café, trigo      |

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
- Images are stored in `storage/app/public/diagnosticos/` via `Storage::disk('public')`.
- All user-facing strings are in Spanish.
- No JS frameworks — plain Blade + Alpine.js if lightweight interactivity is needed.

## Notes

- `AGENT.md` and `PROMPTS.md` contain project context and prompt templates for AI-assisted development.
- Never commit `GEMINI_API_KEY` — it lives only in `.env` (gitignored).
