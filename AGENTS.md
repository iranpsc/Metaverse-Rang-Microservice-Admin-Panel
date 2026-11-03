# AGENTS.md

## Project at a glance

Metarang Admin Panel is a Laravel application that exposes a JSON API to a
Vue 3 single-page admin UI. The SPA is served by Laravel and built with Vite.
The product contains Persian UI copy and Jalali/Persian date support; preserve
existing RTL and localized-text behavior when changing the frontend.

### Technology stack

- **Backend:** PHP 8.4, Laravel 13, Laravel Sanctum, Eloquent.
- **Authorization and observability:** Spatie Permission, Spatie Activitylog,
  policies, and custom admin/phone-verification middleware.
- **Data and infrastructure:** MySQL in normal environments; Redis,
  queueable jobs, FTP storage, and SQLite-backed translation data where
  applicable. Tests use in-memory SQLite.
- **Frontend:** Vue 3, Vue Router, Pinia, PrimeVue, Tailwind CSS 4, Axios,
  CKEditor, Vite 8, and JavaScript with small TypeScript utility modules.
- **Testing and quality:** PHPUnit 12, Laravel test helpers, factories,
  fakes, Mockery, Laravel Pint, and PCOV in CI.
- **Local runtime:** Docker Compose is the recommended environment. See
  `README.md` and `docker/README.md`; the app container is named `app`.

`composer.json` and `package.json` are the source of truth for versions. Do
not infer package versions from prose in the README.

## Repository map

```text
app/
  Http/Controllers/Api/  API endpoints (mostly thin controllers)
  Http/Requests/         request validation, grouped by feature
  Http/Resources/        API serialization, grouped by feature where useful
  Models/                Eloquent models and relationships
  Services/              feature/domain operations
  Repositories/          read/query abstractions used by selected domains
  Jobs/, Notifications/, Policies/, Rules/, Observers/
database/
  migrations/, factories/, seeders/
resources/js/
  pages/                 route-level Vue views, organized by domain
  components/            reusable UI and domain components
  composables/           reusable Vue state/behavior and API orchestration
  router/                route definitions, guards, sidebar/menu roles
  store/                 Pinia stores
  utils/                 API client and focused helpers
routes/api.php           API registration and authorization boundaries
tests/Feature/           HTTP/API behavior tests, organized by domain
tests/Unit/              focused model, service, job, rule, and repository tests
tests/Concerns/          reusable test payload/schema/auth helpers
docs/                    webhook API documentation and OpenAPI artifact
```

## Backend architecture and conventions

### Request lifecycle

Use the established flow for new or changed API behavior:

1. Register the route in `routes/api.php`, inside the appropriate auth and
   role middleware group.
2. Keep the controller focused on input/output orchestration.
3. Put validation in a dedicated `FormRequest` under
   `app/Http/Requests/<Domain>/`.
4. Put non-trivial business logic, transactions, file handling, and external
   integrations in a `Service` under `app/Services/<Domain>/`.
5. Serialize models through an `Http\Resources` class instead of returning
   raw models.
6. Add or update focused tests before considering the change complete.

Controllers normally use constructor-injected, `readonly` services and return
explicit `JsonResponse` objects. Typical success envelopes are:

```json
{
  "success": true,
  "data": {},
  "message": "Persian user-facing message"
}
```

For paginated listing endpoints, preserve the existing `data.<items>` plus
`data.pagination` shape (`current_page`, `last_page`, `per_page`, `total`,
`from`, `to`). Reuse the closest endpoint's naming and response structure;
API consistency is more important than introducing a new generic abstraction.

### Routes, authentication, and authorization

- Most admin endpoints live under the shared `auth:sanctum`,
  `EnsureAdminSanctumAuth`, and `CaptureContentChanges` middleware group.
- Role checks belong on the route group (for example,
  `role:level-management|super-admin`), not only in the Vue UI. Keep server
  authorization authoritative.
- Use route-model binding where the route and controller already follow that
  pattern; otherwise preserve the surrounding feature's explicit-ID approach.
- The frontend menu and router guards are derived from
  `resources/js/router/menuConfig.js`; add/update menu configuration and route
  metadata together with a protected page, but never treat it as a replacement
  for backend authorization.

### Models, queries, and changes to data

- Add schema changes through timestamped migrations; do not edit old
  migrations that may already be deployed.
- Update factory data whenever model-required fields or important relationships
  change. Tests expect factories to produce valid records by default.
- Keep relationship loading intentional. Services commonly eager-load needed
  relationships and resources define the public JSON contract.
- Prefer a service for multi-step writes, file operations, webhooks, or
  cross-model work. Use database transactions where an operation must be
  atomic.
- Preserve side effects such as activity logging, content-webhook recording,
  jobs, and storage cleanup. Inspect the neighboring domain before adding a
  mutation.
- For uploads, validate files in the request, store through the configured
  disk, and test with `Storage::fake()` / disk assertions. Delete replaced or
  orphaned files deliberately.

### PHP style

- Follow `.editorconfig`: UTF-8, LF, four spaces, final newline, no trailing
  whitespace.
- Use Laravel/PHP conventions: strict, descriptive types where practical;
  `FormRequest::validated()` rather than unchecked request data; named resource
  classes; and clear service method names such as `create`, `update`, `delete`.
- Follow local style in the file being edited. Run `vendor/bin/pint` on changed
  PHP files before delivery.
- Do not place secrets in code, test fixtures, documentation, or commits. Keep
  `.env` private and base new configuration on `.env.example`.

## Frontend conventions

- Use Vue 3 Composition API patterns already present in pages and composables.
  Keep route-level views in `resources/js/pages/<domain>/` and extract shared
  behavior into `composables/` or shared components rather than duplicating it
  across pages.
- Use the central API client from `resources/js/utils/api.ts` (re-exported by
  `api.js`) so auth headers and shared error handling remain consistent. Do not
  create ad hoc Axios instances in individual pages.
- Reuse components from `resources/js/components/ui/` before introducing a
  new primitive. Check its README and barrel export when using or extending
  the UI library.
- New pages need a lazy-loaded router entry, Persian `meta.title`, and matching
  navigation/menu role handling when applicable. Add the corresponding server
  endpoint and permission enforcement as part of the same feature.
- Preserve existing user-facing language, date formatting, and RTL behavior.
  Use the dedicated date, number, text, sanitization, and API-error utilities
  instead of reimplementing their behavior.
- Use Pinia only for shared application state (such as auth, theme, toast, and
  phone-verification state); prefer local component state for a single page.

## Tests

### Structure and expectations

- **Feature tests** in `tests/Feature/<Domain>/` exercise actual JSON routes:
  authentication, role access, validation, pagination, response contracts,
  persistence, and observable side effects.
- **Unit tests** in `tests/Unit/<Domain>/` exercise services, models,
  repositories, rules, jobs, notifications, and focused helpers without an
  HTTP route when appropriate.
- **Shared test helpers** belong in `tests/Concerns/`. Reuse helpers such as
  `ActsAsSuperAdmin` and the `Creates*ApiSchema` traits rather than copying
  authorization or standard payload setup.
- Test names are descriptive `test_*` methods. Follow the close-by feature's
  fixtures, factories, and response assertions.

The PHPUnit environment is SQLite in-memory with array cache/mail, sync queue,
and no Telescope. Do not write a test that assumes MySQL-only behavior unless
the feature explicitly requires a separate integration environment.

CI enforces **at least 98% coverage** for `app/` and builds the frontend. New
production branches must be covered; do not lower the threshold or hide code
from coverage merely to satisfy the gate. For API changes, include successful,
unauthenticated/unauthorized, validation/error, and important side-effect
coverage as relevant.

### Commands

Run the narrowest relevant check while iterating, then the broader suite needed
for confidence:

```powershell
# PHP formatting
vendor/bin/pint

# One test class or directory
php artisan test --filter=VideoCategoriesApiTest
php artisan test tests/Feature/Videos

# Full PHP test suite
composer test

# Coverage gate used by CI
composer test:coverage

# Frontend production build
npm run build
```

In Docker, prefix PHP/Composer commands with
`docker compose exec app`, for example:
`docker compose exec app php artisan test`.

## Change checklist for agents

Before finishing a change:

1. Inspect adjacent code and tests; match their domain terminology, endpoint
   shape, and authorization strategy.
2. Keep migrations, model fillable/casts/relations, factory data, resources,
   routes, server authorization, frontend route/menu behavior, and tests in
   sync when the feature spans those layers.
3. Do not modify generated directories or runtime artifacts (`vendor/`,
   `node_modules/`, `coverage/`, `storage/` contents) unless the task
   explicitly requires it.
4. Do not overwrite or revert unrelated working-tree changes. This repository
   may be intentionally dirty during collaborative work.
5. Run Pint for PHP changes, the relevant PHPUnit tests, and `npm run build`
   for frontend changes. Report checks that could not be run and why.
6. Review the final diff for accidental `.env`, credentials, debug output,
   translated text regressions, or changed API response contracts.

## Domain-specific notes

- The translations module manages client/game strings, not Laravel `lang/`
  files. Its data is SQLite-backed and has a locale → modal → tab → field
  hierarchy. Structural translation changes synchronize across locales; field
  text is locale-specific. Read `README.md` and `docs/` before altering that
  workflow.
- Content webhooks have dedicated services, tests, Markdown documentation, and
  `docs/content-webhooks.openapi.json`. Keep these artifacts aligned whenever
  their contract changes.
- Queue work, mail, SMS, FTP, and remote service calls must remain testable
  with fakes or mocks; avoid real network calls in automated tests.
