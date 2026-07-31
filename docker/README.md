# Docker

## Production build (verified locally)

```bash
docker compose build app
docker compose up -d
curl -I http://127.0.0.1:8088/
```

The image includes Laravel source, Composer `--no-dev` vendor, and Vite `public/build`. Compose does **not** bind-mount host source.

## Deploy on Dokploy

### 1. Create the application

1. In Dokploy → **Create Service** → **Docker Compose**.
2. Connect your Git repository (this project).
3. Set **Compose file** to `docker-compose.yml` (do **not** use `docker-compose.dev.yml`).
4. Set the build/context root to the repo root (where `artisan` and `docker-compose.yml` live).

### 2. Environment variables

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

LOG_CHANNEL=stderr
LOG_LEVEL=error
```

Generate `APP_KEY` once (locally or in a one-off container):

```bash
php artisan key:generate --show
```

`REDIS_HOST=redis` must stay as the Compose service name. Point `DB_*` at your MySQL (Dokploy MySQL service, managed DB, or host).

### 3. Networking / domain

1. Expose the **`app`** service (container port **8080**, host `${APP_PORT:-8088}`).
2. In Dokploy, attach your domain to the `app` service and enable HTTPS (Traefik/Caddy as configured by Dokploy).
3. Set `APP_URL` to that public HTTPS URL.

Optional: stop publishing Redis to the host in production by removing the `redis.ports` mapping in a Dokploy override, or leave it and firewall the port.

### 4. Deploy

1. Trigger **Deploy** in Dokploy (builds `docker/php/Dockerfile` with context `.`).
2. Wait until logs show: `Server running on [http://0.0.0.0:8080]`.
3. Run migrations once:

```bash
docker compose exec app php artisan migrate --force
```

(Or use Dokploy’s “Execute command” on the `app` container.)

4. Optional queue worker + scheduler:

```bash
docker compose --profile queue up -d
```

In Dokploy, enable the `queue` Compose profile if the UI supports profiles; otherwise add a second compose override that drops the `profiles:` keys for `queue` / `scheduler`.

### 5. Post-deploy checks

```bash
docker compose ps
docker compose logs -f app
docker compose exec app php artisan about
curl -I https://your-domain.example
```

### Important notes

| Topic | Detail |
| --- | --- |
| Compose file | Use **`docker-compose.yml` only** on Dokploy |
| Local bind-mount | Use `docker-compose.dev.yml` only on your laptop |
| Registry | Default base images: `docker.arvancloud.ir`. Override with `DOCKER_REGISTRY` if needed |
| Persistence | Named volume `app-storage` → `/var/www/html/storage` |
| MySQL | Not in this compose file — provide externally |
| `artisan serve` | Fine for small deployments; for heavy traffic prefer php-fpm + nginx later |

## Local development

```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```
