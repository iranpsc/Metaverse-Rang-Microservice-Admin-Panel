# Docker build and runtime optimization report

Date: 2026-10-29

## Summary

The project now has one Compose entry point, `docker-compose.yml`. The former
`docker-compose.dev.yaml` was removed. Frontend assets are built once during
the multi-stage image build, and the same baked production image is reused by
the app, queue, and scheduler services.

## Before and after

| Area | Before | After | Result |
| --- | --- | --- | --- |
| Compose entry points | `docker-compose.yml` plus `docker-compose.dev.yaml` | `docker-compose.yml` only | One supported workflow and less configuration drift |
| Node service | A profile-based Node service could install dependencies and run a separate build against a bind mount | Removed; Vite runs in the Dockerfile frontend stage | Fewer runtime containers and no duplicate dependency installation |
| Composer dependencies | Vendor stage used `--prefer-install=source` | Vendor stage uses `--prefer-dist` | Smaller/faster dependency artifacts and better cache reuse |
| PHP runtime | Composer binary was copied into the runtime image and the entrypoint could install dependencies at startup | Composer is build-only; vendor is baked into the image | Smaller runtime surface and deterministic startup |
| Autoloading | Runtime image ran `composer dump-autoload` again | Autoload optimization is completed in the vendor stage | Removes duplicate startup/build work |
| Build caching | Cache mounts existed, but the dev flow rebuilt dependencies separately | Composer, npm, and apt cache mounts are used by the single image build | Warm rebuilds reuse dependency and package layers |
| Runtime logs | No shared rotation policy in the Compose service defaults | All PHP services use 10 MB / 3-file JSON log rotation | Limits unbounded container log growth |
| Migration control | `RUN_MIGRATIONS` was documented but not passed through Compose | `RUN_MIGRATIONS` is part of the shared environment | Smoke tests and deployments can explicitly control migrations |

## Measurements

Measured on the local Docker daemon on 2026-10-29:

- Optimized cold build: **681.6 seconds**. This included uncached base-image
  downloads and PHP extension compilation.
- Optimized warm build: **12.9 seconds**.
- Final image: **815,512,812 bytes (777.7 MiB)**.
- Vite production build: completed successfully inside the image build; Vite
  reported `built in 16.22s` for the bundle step.
- Stack startup after image build: **41.4 seconds** until Compose started all
  services; app health then reported healthy.
- HTTP smoke test: `GET http://127.0.0.1:8088/` returned **HTTP 200** in
  **0.283 seconds**.

The original image was not present in the local Docker cache, so an exact
before/after byte-size reduction could not be measured without rebuilding the
unmodified image. The comparison above therefore separates measured results
from structural optimizations.

## Runtime verification

The following services were running and healthy/active after the change:

- `app` — healthy, published on port 8088
- `queue` — running
- `scheduler` — running
- `redis` — healthy

The local smoke test used the existing shared microservice MySQL service via
`metarang-mysql` and set `RUN_MIGRATIONS=0`, because the shared database schema
is managed by the microservice stack. Repository-local storage and SQLite paths
were used for persistence during the test.

## Files changed

- `docker-compose.yml`
- `docker/php/Dockerfile`
- `docker/php/entrypoint.sh`
- `docker/README.md`
- Removed `docker-compose.dev.yaml`
