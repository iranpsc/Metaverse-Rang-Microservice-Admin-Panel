# Docker

Two Compose files:

| File | Purpose |
| --- | --- |
| `docker-compose.yml` | Production (Dokploy / local smoke test) |
| `docker-compose.dev.yaml` | Local development (MySQL, Redis, Mailpit, Vite HMR) |

Both build `docker/php/Dockerfile` with **context at the project root** (where `artisan` lives).

Base images are pulled from **Docker Hub**: `php:8.4-fpm-bookworm`, `composer:2`, `node:22-alpine`, `redis:7-alpine`, `mysql:8.0`, `axllent/mailpit:v1.21`.

The Dockerfile follows Docker multi-stage + BuildKit practices ([best practices](https://docs.docker.com/build/building/best-practices/), [multi-stage](https://docs.docker.com/build/building/multi-stage/)): named stages, cache mounts for apt/Composer/npm, sorted packages, production PHP ini baked into the image.

## Production build (verified locally)

On Dokploy, persistence uses **`../files/`** bind mounts (see below). For a **local** production smoke test, override paths so they point at the repo:

```bash
# .env or shell
HOST_STORAGE_PATH=./storage
HOST_DATABASE_PATH=./database
```

On Dokploy, the external networks `dokploy-network` (created by Dokploy) and `metarang-shared` (create once on the host) are required. For a **local** production smoke test:

```bash
docker network create dokploy-network
docker network create metarang-shared
```

Then:

```bash
docker compose build app
docker compose up -d
curl -I http://127.0.0.1:8088/
```

The image includes Laravel source, Composer `--no-dev` vendor, and Vite `public/build`. Default mode does **not** bind-mount host source (only `storage` + `database`). PHP `production.ini` is baked into the image (not mounted from the repo).

## Deploy on Dokploy

### 1. Create the application

1. In Dokploy → **Create Service** → **Docker Compose**.
2. Connect your Git repository (this project).
3. Set **Compose file** to `docker-compose.yml`.
4. Set the build/context root to the repo root (where `artisan` and `docker-compose.yml` live).

Deploy the **microservices** Compose app first so MySQL is up. On the host, create the shared network once (Dokploy already has `dokploy-network`):

```bash
docker network create metarang-shared
```

Do **not** enable Isolated Deployment for this app or the microservices app.

### 2. Persistence (`../files` bind mounts)

Dokploy layout for Compose apps:

```text
/<application-name>/
  code/     ← git clone (compose file lives here)
  files/    ← persistent bind-mount sources
```

Per [Dokploy Compose volumes](https://docs.dokploy.com/docs/core/docker-compose) and [volumes troubleshooting](https://docs.dokploy.com/docs/core/troubleshooting/volumes-mounts):

| Do | Don't |
| --- | --- |
| `../files/storage:/var/www/html/storage` | Absolute `/opt/...` host paths (cleaned / fragile on deploy) |
| `../files/database:/var/www/html/database` | Repo-relative `./storage` or `./docker/...` (wiped on AutoDeploy `git clone`) |
| Bake config into the image | Mount `./docker/php/production.ini` from the repo |

Compose defaults (no env override needed on Dokploy):

| Host path (relative to `code/`) | Container path | Purpose |
| --- | --- | --- |
| `../files/storage` | `/var/www/html/storage` | Uploads (`app/public`), logs, cache |
| `../files/database` | `/var/www/html/database` | Translation SQLite (`database.sqlite`) |

`create_host_path: true` lets Docker create empty `files/storage` and `files/database` if missing. The entrypoint then recreates Laravel subdirs when the mount starts empty.

Optional overrides (local smoke tests only):

```env
HOST_STORAGE_PATH=./storage
HOST_DATABASE_PATH=./database
```

**Named volumes alternative** (Dokploy Volume Backups only work with named volumes, not `../files` binds):

```yaml
volumes:
  - type: volume
    source: laravel-storage
    target: /var/www/html/storage
  - type: volume
    source: laravel-database
    target: /var/www/html/database
```

### 3. Seed existing data (optional)

Find the Dokploy app directory on the host (often under `/etc/dokploy/compose/<app>/` or similar), then copy into `files/`:

```bash
# Adjust APP_DIR to your Dokploy compose app path
APP_DIR=/etc/dokploy/compose/<your-app>

scp database/database.sqlite user@dokploy-host:"$APP_DIR/files/database/database.sqlite"
rsync -avz --progress storage/app/public/ user@dokploy-host:"$APP_DIR/files/storage/app/public/"
```

Then on the host:

```bash
sudo chown -R 33:33 "$APP_DIR/files"
```

(`33` is `www-data` in the PHP image.)

### 4. Environment variables

Add a production `.env` in Dokploy (Environment / Secrets). Minimum:

```env
APP_NAME=AdminHamgit
APP_ENV=production
APP_KEY=base64:GENERATE_WITH_artisan_key_generate
APP_DEBUG=false
APP_URL=https://your-domain.example

APP_PORT=8088

DB_CONNECTION=mysql
DB_HOST=metarang-mysql
DB_PORT=3306
DB_DATABASE=metarang_db
DB_USERNAME=metarang_user
DB_PASSWORD=same-as-MYSQL_PASSWORD-on-microservices

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

FILESYSTEM_DISK=local

LOG_CHANNEL=stderr
LOG_LEVEL=error
```

Generate `APP_KEY` once (locally or in a one-off container):

```bash
php artisan key:generate --show
```

`REDIS_HOST=redis` must stay as **this** Compose service name (Laravel sessions/cache). Do **not** point it at the microservices Redis. `DB_HOST=metarang-mysql` is the network alias of MySQL in the microservices stack (`metarang-shared`). Credentials must match that stack's `MYSQL_*` values.

Translation models use a separate **sqlite** connection at `database/database.sqlite` (persisted under `../files/database`). Do **not** set `DB_CONNECTION=sqlite`.

### 5. Networking / domain

1. Expose the **`app`** service (container port **8080**, host `${APP_PORT:-8088}`).
2. In Dokploy, attach your domain to the `app` service and enable HTTPS (Traefik/Caddy as configured by Dokploy).
3. Set `APP_URL` to that public HTTPS URL (upload URLs are `{APP_URL}/uploads/...`).

Production Redis is **not** published to the host; it is reachable only on the internal `admin-panel-private` network.

### 6. Deploy

1. Trigger **Deploy** in Dokploy (builds `docker/php/Dockerfile` with context `.`).
2. Wait until logs show: `Server running on [http://0.0.0.0:8080]`.
3. Run migrations only if this environment's Laravel `migrations` table matches this app. The microservices `metarang_db` already contains the shared schema — a blind `migrate --force` can alter Go-owned tables.

```bash
# Translation SQLite schema (skip if you already copied a populated database.sqlite)
docker compose exec app php artisan migrate --database=sqlite --force
```

(Or use Dokploy’s “Execute command” on the `app` container.)

### 7. Post-deploy checks

```bash
docker compose ps
docker compose logs -f app
docker compose exec app php artisan about
docker compose exec app ls -lah /var/www/html/database/database.sqlite
docker compose exec app ls -lah /var/www/html/storage/app/public | head
docker compose exec app ls -lah /var/www/html/public/uploads
curl -I https://your-domain.example
```

### Important notes

| Topic | Detail |
| --- | --- |
| Compose files | **`docker-compose.yml`** (production) · **`docker-compose.dev.yaml`** (local dev) |
| Dockerfile context | Project root for both compose files (`context: .`, `dockerfile: docker/php/Dockerfile`) |
| Base images | Official Docker Hub: `php`, `composer`, `node`, `redis`, `mysql`, `mailpit` |
| Persistence | Dokploy `../files/{storage,database}` by default; local override with `HOST_*` |
| Config mounts | PHP ini is **baked** into the image — do not mount from the repo on AutoDeploy |
| External network | Production `app` joins **`dokploy-network`** (Traefik) and **`metarang-shared`** (MySQL) |
| SQLite | `pdo_sqlite` enabled; Translation models use `database/database.sqlite` |
| Uploads | `storage/app/public` ↔ `public/uploads` (created by entrypoint `storage:link`) |
| MySQL | Not in production compose — connect via `DB_HOST=metarang-mysql`; included in `docker-compose.dev.yaml` only |
| Bootstrap cache | Entrypoint clears stale `bootstrap/cache/*.php` on startup |
| `artisan serve` | Fine for small deployments; for heavy traffic prefer php-fpm + nginx later |

## Local development

Use **`docker-compose.dev.yaml`**. It bind-mounts the project tree, runs MySQL/Redis/Mailpit, and starts Vite on port 5173. Builds with `PHP_INI_FILE=local.ini`.

```bash
docker compose -f docker-compose.dev.yaml up -d --build
```

Default URLs:

| Service | URL |
| --- | --- |
| App | http://localhost:8080 |
| Vite | http://localhost:5173 |
| Mailpit UI | http://localhost:8025 |

Stop the stack:

```bash
docker compose -f docker-compose.dev.yaml down
```

On Windows bind mounts, the first app health check can take 1–3 minutes; the compose file uses an extended `start_period` for this.

### Production compose with live source (optional)

To bind-mount source on **`docker-compose.yml`** and run a one-shot Vite build instead of the dev stack:

```env
HOST_STORAGE_PATH=.
HOST_STORAGE_TARGET=/var/www/html
HOST_DATABASE_PATH=./database
HOST_APP_PATH=.
COMPOSE_PROFILES=dev
```

```bash
docker network create dokploy-network   # if missing locally
docker network create metarang-shared   # if missing locally
docker compose up -d --build
```

This live-source mode is for **local** use only — never use `./` repo binds on Dokploy AutoDeploy.
