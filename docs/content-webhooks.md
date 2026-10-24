# Batched content publication

The admin records content changes immediately, but sends only after a manual trigger or midnight **Asia/Tehran**. All admins share the same pending pack. Table filters never restrict publication. Historical activity entries are not backfilled.

## Setup

1. Run `php artisan migrate`. The content webhook migration creates the primary delivery tables and the source outbox on the translation SQLite connection. Back up both databases together. Rollback removes publication history.
2. Configure `WEBHOOK_URL` and `WEBHOOK_SECRET` in the deployment environment. The sender uses `Authorization: Bearer <WEBHOOK_SECRET>` with a JSON body containing `batch_id` and `version`; it sends no custom Metarang headers. Leave `CONTENT_WEBHOOKS_ENABLED=false` until the receiver passes integration testing. Never commit the secret.
3. Run a supervised dedicated worker: `php artisan queue:work content-webhooks --queue=content-webhooks --sleep=1 --timeout=45 --tries=0`. This uses the database driver regardless of the application's default queue. The existing `jobs` table must be migrated.
4. Run Laravel's scheduler every minute (`php artisan schedule:run`, or the deployment's existing scheduler). Midnight delivery is configured in `routes/console.php`; the recovery task only resumes already authorized cycles and projects committed changes.
5. Grant `send-content-webhooks` (admin guard) to appropriate admins through existing permission management. Super-admins already have access. Viewing logs alone does not permit sending everyone's changes.
6. Implement and stage-test the external receiver using `content-webhooks.openapi.json`, then set `CONTENT_WEBHOOKS_ENABLED=true` and restart long-running workers after configuration changes.

The migration has been applied to the local development databases. No real receiver is configured or contacted as part of implementation verification.

## Data integrity and delivery

Relevant admin mutation requests are wrapped in a transaction on their source database. Level, calendar, version and video observers write a source event alongside the mutation. Translation requests compare dictionaries and hierarchy before/after the mutation on SQLite, including bulk imports/deletions. An idempotent projector writes the existing Spatie activity log and a publication record on the primary connection; receipts make replay safe after interruption between databases. A failed projection is recovered by the scheduler and before dispatch.

Each trigger captures a change-ID cutoff. Repeated edits are grouped by feature, entity, resource and language; the top-level route array is deduplicated. Audit entries remain separate. Membership and payload are committed before network delivery. New changes never join an in-flight pack. Empty triggers consume no version.

The first pack is `V1.1.1`. A receiver acknowledgement advances the next patch exactly once. A failed pack blocks newer packs, and both manual and midnight triggers retry the oldest first. An authorized recovery cycle can send an old failed pack followed by one newer pack. Five transport attempts use delays of 10, 30, 120 and 300 seconds. Network failures, 408, 429 and 5xx are retryable; other rejected responses fail immediately. Status is pending during retries, failed on exhaustion/rejection, and delivered only after the matching acknowledgement. Delivery disabled mid-cycle pauses it without discarding membership.

The sender accepts only HTTP 200 with `{"batch_id":"<same ID>","status":"accepted"}`. A timeout may occur after receiver acceptance: the receiver MUST persist batch IDs and safely acknowledge identical retries. Delivered means invalidation accepted, not page rendering finished. Do not expire receiver deduplication records while a sender pack can still be retried.

The database serializes delivery acknowledgement and version advancement. The projector uses a separate lock so normal writes on the primary MySQL database do not wait for HTTP. SQLite-only deployments serialize database writes more broadly; production should use the existing primary MySQL connection. Translation outbox data stays on SQLite. Application code outside these admin request paths must use a source transaction when making tracked model changes. Bulk SQL bypassing Eloquent must explicitly record a source event.

Preserve pending/failed activity records and source receipts when configuring retention or Spatie cleanup. Publication metadata contains administrator identities; protect it with the same access controls as activity logs. HTTP errors are summarized rather than exposing arbitrary receiver response bodies.

## Endpoint bindings

The registry is `App\Services\ContentWebhooks\RouteMapper`. Paths follow `api-endpoints.json`, not the `/api/v1` paths in the original examples. Identifiers are resolved and path segments encoded before capture. A parent path does not implicitly invalidate children.

| Admin content | Invalidation identifiers |
| --- | --- |
| Level create/delete or list-visible name, slug, score, image/background edits | `/api/levels` plus the affected `/api/levels/{id}` |
| Level general-info, gem, gift, licenses, prize | `/api/levels/{id}/{resource}` plus `/api/levels/{id}`; no unrelated level list refresh |
| Level deletion | Level collection, detail and all five subresources |
| Calendar | `/api/calendar` and `/api/calendar/{id}` |
| Versions (`Calendar.is_version`) | Calendar collection/detail plus `/api/calendar/latest-version` |
| Videos | `/api/tutorials`, `/api/video-tutorials`, `/api/tutorials/search`, `/api/tutorials/{slug}`, containing category and subcategory collections |
| Video deletion | Video targets plus `/api/tutorials/{video}/interactions` (numeric video ID) |
| Video categories | `/api/tutorials/categories`, `/api/tutorials/categories/{slug}`, `/api/tutorials/categories/{slug}/videos`; deletion includes descendants |
| Video subcategories | Category listing, old/new parent category detail/video collection and `/api/tutorials/categories/{category}/{subcategory}` |
| Translations | `/lang/{locale}.json` only for languages whose content or publication status changed |

Next.js must map these identifiers to its actual paths/tags, including every query/pagination variant of collection and search caches. Category/subcategory placeholders are slugs. Visitor interactions/views do not trigger admin publication. Deleted and moved content retains old targets in the immutable event.

Translation additions/edits ignore empty replicated placeholders in other languages. Shared deletions include languages containing the deleted content. Hierarchy-only changes create audit entries without pending delivery records. Before each delivery, affected language files are atomically regenerated from current committed dictionaries; deleted/disabled languages have their exported file removed. Null placeholders are excluded from these regenerated files. The system invalidates current data, rather than publishing historical snapshots from each batch. Translation model versions remain independent of delivery versions.

## Verification

For the standalone temporary Node receiver, run `node scripts/webhook-receiver.cjs`.
It listens only on `http://127.0.0.1:4900`; inspect deliveries at `/__test/requests`
and readiness at `/health`. The destination is `/api/webhooks/cache-revalidate`.
The default secret is the synthetic `local-webhook-test-secret`; do not use it in production.
No dependencies or real application environment changes are needed.

Run all receiver-backed tests in a second PowerShell window:

```powershell
$env:CONTENT_WEBHOOK_NODE_RECEIVER='1'
$env:CONTENT_WEBHOOK_SIMULATION='1'
php vendor/bin/phpunit tests/Feature/ContentWebhooks --no-coverage
Remove-Item Env:CONTENT_WEBHOOK_NODE_RECEIVER, Env:CONTENT_WEBHOOK_SIMULATION
```

`CONTENT_WEBHOOK_SIMULATION` enables the database-queue transport scenario shared
with the Postman simulation. `CONTENT_WEBHOOK_NODE_RECEIVER` enables Node-specific
failure, captured-request, lifecycle and subprocess tests. These use isolated databases;
translation publication uses temporary storage. Stop the receiver with Ctrl+C.

Append `?failure=503`, `401`, `408`, `429`, `500`, `422`, `409`, `html`, `bad-ack`,
`disconnect`, `timeout`, `accepted-timeout`, or `slow` to inject transport behavior.
`accepted-timeout` records acceptance before withholding the acknowledgement.
POST `/__test/reset` clears in-memory captures and accepted IDs; POST `/__test/mode`
with `{"mode":"success"}` sets the default mode. Captures are also appended to
`storage/logs/webhook-receiver.ndjson`; restarting/resetting does not remove that file.
Receiver idempotency is intentionally in-memory for this temporary test app, whereas
the real Next.js receiver must persist accepted batch IDs across restarts.

The Node server and Postman simulation use the same port; run only one at a time.
See `content-webhooks-verification.md` for coverage and remaining integration gates.

Run the application tests with `php vendor/bin/phpunit tests/Feature/ContentWebhooks --no-coverage`. The real-HTTP test is opt-in and uses an isolated in-memory test database and a test-only secret.

Start the contract-derived local receiver:

```text
postman simulation run postman/simulations/content-webhooks.sim.yaml --port 4900 --output ndjson
```

Then on PowerShell:

```powershell
$env:CONTENT_WEBHOOK_SIMULATION='1'
php vendor/bin/phpunit tests/Feature/ContentWebhooks/ContentWebhookTest.php --filter real_database_queue --no-coverage
Remove-Item Env:CONTENT_WEBHOOK_SIMULATION
```

The local simulation checks the bearer token, JSON contract and duplicate identity; its `failure=503`, `failure=401` and `failure=timeout` query modes exercise error paths. It is built from the agreed contract. Lookup of an owning-team mock was unavailable because Postman CLI was signed out; no cloud mock or webhook resource was created.

Watch the admin summary for pending count/age, oldest failed pack and last successful delivery. Worker logs contain batch IDs and delivery outcomes. Before enabling production, verify the real receiver's route/tag mapping, authentication, duplicate handling, and language loading. Local mock success does not establish that external integration.
