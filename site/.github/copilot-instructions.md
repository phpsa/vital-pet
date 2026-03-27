# Vital Store Development Instructions

This file defines coding guidance and near-term priorities for work in this repository.

## Project Context

- Framework: Laravel (currently 10/11 compatible via composer constraint)
- Commerce engine: Lunar (`lunarphp/lunar`)
- Starter: Lunar Livewire starter kit
- Main app root: `site/`
- Backend language: PHP 8.2+
- Frontend/build tooling: Vite, Tailwind CSS, Livewire
- Common folders:
  - `app/` business logic, controllers, models, Livewire components
  - `config/` framework and package configuration (including `config/lunar/`)
  - `database/` migrations, factories, seeders
  - `resources/` Blade views, JS, CSS
  - `routes/` web, api, console, channels
  - `tests/` feature and unit tests

## Authoritative References

- Lunar docs: https://docs.lunarphp.com/
- Lunar LLM-friendly reference: https://docs.lunarphp.com/llms.txt

Use Lunar docs first for any Lunar-specific implementation, upgrades, extension points, and API behavior.

## Immediate Roadmap (Priority Order)

1. Upgrade Laravel to 12.
2. Upgrade Lunar to the latest compatible version.

Do not start large feature work until these upgrades are complete or explicitly deferred.

## Upgrade Execution Guidance

- Make dependency upgrades in small, reviewable commits.
- After each upgrade step:
  - Run Composer install/update cleanly.
  - Run migrations and verify no schema/runtime regressions.
  - Run tests and fix failures before proceeding.
- Prefer package and framework upgrade guides over ad-hoc fixes.
- Keep changes minimal and scoped to upgrade requirements.
- Document any required manual follow-up in `README.md` or a dedicated upgrade note.

## Coding Expectations

- Preserve existing architectural patterns in this codebase.
- Prefer framework-native patterns over custom abstractions when reasonable.
- Keep public behavior stable unless the task explicitly requires behavior change.
- Add or update tests when changing behavior, bug fixes, or upgrade-sensitive code.
- Avoid unrelated refactors while performing dependency upgrades.

## Validation Checklist For Changes

- App boots successfully.
- Primary storefront routes render.
- Lunar admin/panel routes still authenticate and render.
- Background jobs, queues, and cache configuration still function.
- Test suite passes (or failures are documented with clear cause and next action).

## Notes For Future Tasks

- If a task touches Lunar internals or package integrations, consult:
  - https://docs.lunarphp.com/
  - https://docs.lunarphp.com/llms.txt
- Keep this file updated as project standards or roadmap priorities evolve.
