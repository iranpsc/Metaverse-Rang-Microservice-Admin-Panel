# Content webhook verification — 2026-10-24

## Fixes established by regression tests

1. Retry requests now validate the requested batch even when a delivery cycle is already active. An unrelated batch returns HTTP 409 rather than an unrelated successful enqueue response.
2. Deleting a level image invalidates the level detail and list, without unnecessarily invalidating all five level subresources. Deleting the level itself still invalidates them.
3. SQLite delivery and projection transactions acquire a write reservation before reading state. Two simultaneous triggers previously produced a stale-snapshot database-lock error; separate-process tests now establish one shared cycle and one outgoing delivery.
4. Category slug changes invalidate old and new descendant subcategory URLs as well as category detail/list identifiers.

## Coverage

Final validation results:

- After switching to `WEBHOOK_URL`, `WEBHOOK_SECRET`, and bearer authentication, the complete webhook suite passed: **51 tests, 412 assertions**, including actual queued HTTP delivery to the updated Node receiver. The revised OpenAPI specification passed Postman syntax lint.

- Broad relevant Laravel run: **720 tests, 3,722 assertions; 719 passed**, one video-upload cleanup failure using a long Windows temporary storage path.
- The upload failure reproduced with that long path and passed with normal storage. All **13 upload tests / 59 assertions** passed with a shorter isolated storage path. No upload implementation was changed.
- Additional member-status API regression: **1 test / 17 assertions passed**. Together with the broad run this covers **51 webhook tests**, including all opt-in real HTTP and subprocess scenarios, with no webhook failures.
- Frontend production build, Node syntax check, Pint check, and changed-file whitespace check passed.
- Browser verification confirmed pending/not-applicable badges and audit details. At a 1,280-pixel viewport, the send button's right edge was x=329 and the category filter's left edge x=605, confirming the required left-side placement. The viewport override was then reset.

For isolated broad runs on Windows, prefer a short storage root such as a unique
directory under `storage/app/wht-<id>` rather than a long user-profile temporary path.
Set `LARAVEL_STORAGE_PATH` for that test process; do not change the application's `.env`.

| Plan area | Verification |
| --- | --- |
| Levels, general information, gem, gift, licenses, prize, level image, calendar, versions, videos, categories, subcategories | `ModuleLifecycleTest`: actual model observers within capture middleware, create/update/delete audit history, timestamp-only suppression, real database queue worker, signed HTTP acceptance, and delivered status through the detail API for every member |
| Old/new URLs, moves, cascade deletion, interactions exclusion | `RouteMapperTest`: category rename, subcategory move, descendant deletion snapshot, video content routes vs deletion interaction target, ordinary calendar vs version |
| Translations | `TranslationWebhookTest`: Persian-only creation ignores English placeholders; shared deletion includes populated languages; hierarchy-only changes are audit-only; import targets selected locale; separate SQLite rollback and idempotent projection; actual dictionary content exists before real HTTP dispatch; publication failure prevents transport |
| Shared batches, grouping and cutoff | `ContentWebhookTest`: repeated edits, multiple historical actors, unique routes, new edits remain unassigned, immutable retry body, oldest failed pack before newer changes |
| Versions and status | First V1.1.1, failed delivery consumes no version, retry advances once, pending/failed/delivered member API responses, empty and disabled delivery |
| Real transport and authentication | Node receiver: bearer token, JSON contract, route union, duplicate identity, bad token, HTTP 401/409/422/408/429/500/503, HTML proxy failure, malformed acknowledgement, socket reset and timeout |
| Lost acknowledgement | Node accepts the batch but delays its acknowledgement beyond the sender timeout; retry carries identical bytes and is acknowledged as a duplicate |
| Races and worker interruption | `ConcurrencyTest`: separate PHP processes and a file-backed SQLite WAL database; simultaneous triggers share cycle; competing workers send once; killed worker after receiver acceptance recovers immutable payload and advances version once |
| Scheduling and recovery | Tehran midnight is due at 20:30 UTC, not 20:29; scheduled actor is null; recovery resumes an active authorized cycle and does not publish untriggered changes |
| Authorization | Unauthenticated requests rejected; log-view permission alone cannot dispatch; explicit send permission and super-admin checks; disabled dispatch rejected |
| Vue page | Production build; browser inspection of disabled send control, all-admin scope explanation, pending and not-applicable badges, historical actor roles, route details and pending metadata |

## Environment and limits

The receiver is `scripts/webhook-receiver.cjs`, a loopback-only standalone Node app.
The actual app remains delivery-disabled; its Next.js destination and secret were not changed.
No real Next.js service was contacted. The existing Postman contract-derived simulation
remains available; the broader fault matrix runs against the user-requested Node server.

Tests use in-memory databases and temporary file-backed databases for subprocess races.
Translation webhook tests now isolate their export storage. During development, an import
fixture briefly overwrote the local English export; it was regenerated from the committed
translation database (1,719 entries). Subsequent regression runs use temporary storage.

Browser checks used existing local audit data and did not dispatch it. Failed-pack retry
and delivery transitions were exercised through authenticated APIs and real workers, but
not by clicking a live enabled browser button. MySQL-specific concurrent locking, deployed
worker supervision, and the real Next.js route/tag mapping remain staging integration gates.
The receiver's accepted-ID store is deliberately temporary; it is not a production receiver.

The frontend build retains the existing Vite configuration-loader warning; compilation succeeds.
