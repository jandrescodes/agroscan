---
name: git-commit
description: 'Execute git commit with conventional commit message analysis, intelligent staging, and message generation. Use when user asks to commit changes, create a git commit, or mentions "/commit". Supports: (1) Auto-detecting type and scope from changes, (2) Generating conventional commit messages from diff, (3) Interactive commit with optional type/scope/description overrides, (4) Intelligent file staging for logical grouping'
license: MIT
allowed-tools: Bash
---

# Git Commit with Conventional Commits

## Overview

Create standardized, semantic git commits using the Conventional Commits specification. Analyze the actual diff to
determine appropriate type, scope, and message.

## Conventional Commit Format

```
<type>[optional scope]: <description>

[optional footer(s)]
```

## Commit Types

| Type       | Purpose                        |
|------------|--------------------------------|
| `feat`     | New feature                    |
| `fix`      | Bug fix                        |
| `docs`     | Documentation only             |
| `style`    | Formatting/style (no logic)    |
| `refactor` | Code refactor (no feature/fix) |
| `perf`     | Performance improvement        |
| `test`     | Add/update tests               |
| `build`    | Build system/dependencies      |
| `ci`       | CI/config changes              |
| `chore`    | Maintenance/misc               |
| `revert`   | Revert commit                  |

## Breaking Changes

```
# Exclamation mark after type/scope
feat!: remove deprecated endpoint

# BREAKING CHANGE footer
feat: allow config to extend other configs

BREAKING CHANGE: `extends` key behavior changed
```

## Project Scopes (AgroScan)

| Scope          | Area                                  |
|----------------|---------------------------------------|
| `diagnostico`  | Diagnosis flow (upload, analyze, show)|
| `gemini`       | GeminiService and prompt              |
| `weather`      | WeatherService and Open-Meteo         |
| `models`       | Eloquent models                       |
| `migrations`   | Database migrations                   |
| `views`        | Blade templates                       |
| `config`       | config/ files and .env               |
| `routes`       | routes/web.php                        |
| `tests`        | Feature and unit tests                |

## Project Git Rules

- Branch naming: `feature/descripcion`
- Never commit directly to `main`
- Never commit `.env` or files containing API keys

## Workflow

### 1. Analyze Diff

```bash
# If files are staged, use staged diff
git diff --staged

# If nothing staged, use working tree diff
git diff

# Also check status
git status --porcelain
```

### 2. Stage Files (if needed)

If nothing is staged or you want to group changes differently:

```bash
# Stage specific files
git add path/to/file1 path/to/file2

# Stage by pattern
git add app/Services/*
```

**Never commit secrets** (.env, credentials, API keys).

### 3. Generate Commit Message

Analyze the diff to determine:

- **Type**: What kind of change is this?
- **Scope**: What area/module is affected? Use the project scopes table above.
- **Description**: One-line summary in **Spanish** (present tense, imperative mood, <72 chars). The type and scope stay
  in English, only the description after `:` is in Spanish.

### 4. Propose and Wait for Approval

**NEVER commit directly.** Always show the proposed commit message first and wait for user confirmation:

```
Commit propuesto:

  <type>[scope]: <descripción en español>

¿Confirmas el commit?
```

Only execute the commit after the user explicitly approves. If the user requests changes to the message, adjust and
propose again before committing.

### 5. Execute Commit (only after approval)

**Always use a single-line commit message** — no body, no footer, no Co-Authored-By.

```bash
git commit -m "<type>[scope]: <descripción en español>"
```

## Examples

```bash
# New feature
feat(diagnostico): implementar carga de imagen y análisis con Gemini Vision

# Bug fix
fix(gemini): manejar JSON inválido en respuesta de la API

# Service added
feat(weather): integrar Open-Meteo para enriquecer diagnóstico con clima

# Migration
chore(migrations): crear tabla diagnosticos con campos de plaga y clima

# Prompt improvement
refactor(gemini): actualizar prompt para priorizar plagas de Santa Cruz

# Config
chore(config): agregar gemini.php con api_key, model y timeout
```

## Best Practices

- **Single line only**: no body, no footer, no Co-Authored-By attribution
- One logical change per commit
- Present tense: "add" not "added"
- Imperative mood: "fix bug" not "fixes bug"
- Keep description under 72 characters

## Git Safety Protocol

- NEVER update git config
- NEVER run destructive commands (--force, hard reset) without explicit request
- NEVER skip hooks (--no-verify) unless user asks
- NEVER force push to main/master
- NEVER commit .env or files with API keys
- If commit fails due to hooks, fix and create NEW commit (don't amend)
