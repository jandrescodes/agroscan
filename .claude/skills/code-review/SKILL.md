---
name: code-review
description: 'Review Laravel code before merging a branch in AgroScan. Use when the user asks to review code, check a PR, validate a feature before merge, or mentions "code review", "revisar código", "antes del merge", or "PR". Triggers automatically when a feature branch is ready to merge.'
license: MIT
allowed-tools: Bash, Read
---

# Code Review — AgroScan

## Overview

Structured code review process for AgroScan before any branch merge. Covers security (API key exposure, file uploads),
Laravel conventions, service layer correctness, and Gemini/Weather integration patterns.

## How to Run the Review

```bash
# See all changed files vs main
git diff main --name-only

# Full diff against main
git diff main

# Check for uncommitted changes
git status
```

Review each changed file against the checklist below. Group findings by severity before reporting.

---

## Review Checklist

### 1. Security — block merge if any fail

- [ ] `GEMINI_API_KEY` not hardcoded — only accessed via `config('gemini.api_key')` or `env()`
- [ ] No secrets committed — `.env`, credentials, tokens not in diff
- [ ] Image validation present — `mimes:jpg,jpeg,png,webp|max:5120` (or equivalent) in FormRequest
- [ ] Blade output escaped — `{{ }}` used everywhere; `{!! !!}` only for trusted internal HTML
- [ ] CSRF token present on all POST forms (`@csrf`)
- [ ] Uploaded files stored in `storage/app/public/diagnoses/` only — no arbitrary paths

---

### 2. Laravel Conventions — block merge if any fail

- [ ] Services injected via constructor — no `app()` or `resolve()` calls inside controllers
- [ ] FormRequest used for validation — no `$request->validate()` inline in controller methods
- [ ] Eloquent fillable defined — `$fillable` set explicitly in every model
- [ ] No raw SQL — Eloquent query builder used; if raw SQL is necessary it uses `DB::select()` with bindings
- [ ] Routes in `routes/web.php` — no route definitions elsewhere
- [ ] Controllers in `app/Http/Controllers/` — no logic in route closures

---

### 3. Service Layer — block merge if any fail

- [ ] `GeminiService::analizar()` exception handled — controller wraps the call in try/catch and redirects with error flash on `\RuntimeException`
- [ ] `WeatherService` failure is graceful — `null` return does not break the diagnostic flow; weather fields persisted as `null`
- [ ] Gemini response validated — JSON decoded and checked against the contract fields (`has_problem`, `pest_name`, `risk_level`, `description`, `immediate_action`, `preventive_action`, `confidence`) before persisting
- [ ] HTTP timeout set — Gemini and Weather HTTP calls have a timeout configured (no indefinite hang)
- [ ] `risk_level` value validated — only `low`, `medium`, `high` accepted; reject or null anything else

---

### 4. Logic & Quality — flag as warning, discuss before merge

- [ ] Prompt mentions Santa Cruz context — Gemini prompt references Bolivia/Santa Cruz and the 5 target pests
- [ ] Image path stored as relative — `image_path` column stores the relative path, not the full absolute filesystem path
- [ ] `confidence` within range — value is between 0.0 and 1.0 before persisting
- [ ] Risk colors consistent in views — `bajo`=green, `medio`=yellow, `alto`=red across all Blade templates
- [ ] No `dd()` / `dump()` left in code
- [ ] Edge cases handled:
    - What if the uploaded image is corrupt or too large?
    - What if Gemini returns partial JSON or extra fields?
    - What if Open-Meteo is unreachable?

---

### 5. Git & Branch — block merge if any fail

- [ ] Branch name follows `feature/descripcion` convention
- [ ] Commits follow Conventional Commits with correct scope
- [ ] No commits directly to `main`
- [ ] No `.env` or API keys in the diff

---

## Output Format

Always structure the review response in this exact format:

```
## Code Review — [branch name]

### ✅ What's good
- [At least 2 specific things done well]

### ⚠️ Observations (non-blocking)
- [Improvements that would be nice but don't block merge]

### 🚨 Must fix before merge
- [File:line] — [Problem description]
  [Code showing the issue]
  Fix: [Code showing the correct approach]
```

If there are no blocking issues, end with:

```
### Verdict: ✅ Approved — ready to merge
```

If there are blocking issues, end with:

```
### Verdict: 🚨 Changes required — do not merge until fixed
```

---

## Quick Reference — Most Common Issues

| # | File type     | Issue                                                              | Severity   |
|---|---------------|--------------------------------------------------------------------|------------|
| 1 | Service       | API key hardcoded instead of `config('gemini.api_key')`            | 🚨 Block   |
| 2 | Controller    | `GeminiService` call not wrapped in try/catch                      | 🚨 Block   |
| 3 | Controller    | `$request->validate()` inline instead of FormRequest               | 🚨 Block   |
| 4 | FormRequest   | Missing image MIME or size validation                              | 🚨 Block   |
| 5 | Service       | Gemini JSON response persisted without contract validation          | 🚨 Block   |
| 6 | View          | `{!! !!}` used on user-controlled data                             | 🚨 Block   |
| 7 | Service       | No HTTP timeout on Gemini or Weather call                          | ⚠️ Warning |
| 8 | View          | Risk badge color doesn't match bajo/medio/alto convention          | ⚠️ Warning |
| 9 | Model         | `$fillable` not defined                                            | ⚠️ Warning |
| 10 | Service       | `dd()` or `dump()` left in code                                   | ⚠️ Warning |
