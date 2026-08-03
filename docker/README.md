# Docker

One Compose file: `docker-compose.yml` (production + local).

## Production build (verified locally)

Prepare host persistence paths first (Dokploy server or local machine):

```bash
sudo mkdir -p \
  /opt/metarang/database \
  /opt/metarang/storage/app/public \
  /opt/metarang/storage/framework/cache/data \
  /opt/metarang/storage/framework/sessions \
  /opt/metarang/storage/framework/views \
  /opt/metarang/storage/logs
sudo chown -R 33:33 /opt/metarang
sudo chmod -R ug+rwX /opt/metarang
```

Then:

```bash
docker compose build app
docker compose up -d
curl -I http://127.0.0.1:8088/
```

The image includes Laravel source, Composer `--no-dev` vendor, and Vite `public/build`. Default mode does **not** bind-mount host source (only `storage` + `database`).

For a local smoke test without `/opt/metarang`, set in `.env` (or the shell):

```bash
HOST_STORAGE_PATH=./storage
HOST_DATABASE_PATH=./database
docker compose up -d --build
```

## Deploy on Dokploy

### 1. Create the application

1. In Dokploy → **Create Service** → **Docker Compose**.
2. Connect your Git repository (this project).
3. Set **Compose file** to `docker-compose.yml`.
4. Set the build/context root to the repo root (where `artisan` and `docker-compose.yml` live).

### 2. Host persistence (`/opt/metarang`)

On the Dokploy **host** (SSH), create directories before the first deploy:

```bash
sudo mkdir -p \
  /opt/metarang/database \
  /opt/metarang/storage/app/public \
  /opt/metarang/storage/framework/cache/data \
  /opt/metarang/storage/framework/sessions \
  /opt/metarang/storage/framework/views \
  /opt/metarang/storage/logs
sudo chown -R 33:33 /opt/metarang
sudo chmod -R ug+rwX /opt/metarang
```

Compose bind-mounts:

| Host path | Container path | Purpose |
| --- | --- | --- |
| `/opt/metarang/storage` | `/var/www/html/storage` | Uploads (`app/public`), logs, cache |
| `/opt/metarang/database` | `/var/www/html/database` | Translation SQLite (`database.sqlite`) |

Optional overrides in Dokploy env:

```env
HOST_STORAGE_PATH=/opt/metarang/storage
HOST_DATABASE_PATH=/opt/metarang/database
```

### 3. Seed existing data (optional)

From your laptop (once):

```bash
scp database/database.sqlite user@dokploy-host:/opt/metarang/database/database.sqlite
rsync -avz --progress storage/app/public/ user@dokploy-host:/opt/metarang/storage/app/public/
```

Then on the host:

```bash
sudo chown -R 33:33 /opt/metarang
```

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
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password

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

`REDIS_HOST=redis` must stay as the Compose service name. Point `DB_*` at your MySQL (Dokploy MySQL service, managed DB, or host).

Translation models use a separate **sqlite** connection at `database/database.sqlite` (persisted under `/opt/metarang/database`). Do **not** set `DB_CONNECTION=sqlite`.

### 5. Networking / domain

1. Expose the **`app`** service (container port **8080**, host `${APP_PORT:-8088}`).
2. In Dokploy, attach your domain to the `app` service and enable HTTPS (Traefik/Caddy as configured by Dokploy).
3. Set `APP_URL` to that public HTTPS URL (upload URLs are `{APP_URL}/uploads/...`).

Optional: stop publishing Redis to the host in production by removing the `redis.ports` mapping in a Dokploy override, or leave it and firewall the port.

### 6. Deploy

1. Trigger **Deploy** in Dokploy (builds `docker/php/Dockerfile` with context `.`).
2. Wait until logs show: `Server running on [http://0.0.0.0:8080]`.
3. Run migrations once:

```bash
# Main MySQL schema
docker compose exec app php artisan migrate --force

# Translation SQLite schema (skip if you already copied a populated database.sqlite)
docker compose exec app php artisan migrate --database=sqlite --force
```

(Or use Dokploy’s “Execute command” on the `app` container.)

4. Optional queue worker + scheduler:

```bash
docker compose --profile queue up -d
```

In Dokploy, enable the `queue` Compose profile if the UI supports profiles; otherwise add a second compose override that drops the `profiles:` keys for `queue` / `scheduler`.

### 7. Post-deploy checks

```bash
docker compose ps
docker compose logs -f app
docker compose exec app php artisan about
ls -lah /opt/metarang/database/database.sqlite
ls -lah /opt/metarang/storage/app/public | head
docker compose exec app ls -lah /var/www/html/public/uploads
curl -I https://your-domain.example
```

### Important notes

| Topic | Detail |
| --- | --- |
| Compose file | Single **`docker-compose.yml`** for Dokploy and local |
| Local bind-mount | Set `HOST_STORAGE_PATH=.` + `HOST_STORAGE_TARGET=/var/www/html` and `COMPOSE_PROFILES=dev` |
| Registry | Default base images: `docker.arvancloud.ir`. Override with `DOCKER_REGISTRY` if needed |
| Persistence | Host `/opt/metarang/{storage,database}` by default; override with `HOST_*` |
| Redis ports | Published on `127.0.0.1` only (`FORWARD_REDIS_PORT`) |
| SQLite | `pdo_sqlite` enabled; Translation models use `database/database.sqlite` |
| Uploads | `storage/app/public` ↔ `public/uploads` (created by entrypoint `storage:link`) |
| MySQL | Not in this compose file — provide externally |
| `artisan serve` | Fine for small deployments; for heavy traffic prefer php-fpm + nginx later |

## Local development

Image-based (same as production, project paths):

```env
HOST_STORAGE_PATH=./storage
HOST_DATABASE_PATH=./database
```

```bash
docker compose up -d --build
```

Live source bind-mount + Vite asset build (`dev` profile):

```env
HOST_STORAGE_PATH=.
HOST_STORAGE_TARGET=/var/www/html
HOST_STORAGE_OPTIONS=:cached
HOST_DATABASE_PATH=./database
COMPOSE_PROFILES=dev
```

```bash
docker compose up -d --build
```

With bind-mount enabled, local `database/` and `storage/` are used; `/opt/metarang` is not required.
