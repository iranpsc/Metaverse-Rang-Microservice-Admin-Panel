# Admin Hamgit

Laravel 12 + Vue 3 admin panel for Hamgit. The backend exposes a JSON API; the frontend is a Vite-powered SPA served from Laravel.

## Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.2, Laravel 12, Sanctum, Spatie Permission / Activity Log |
| Frontend | Vue 3, Vite 8, Pinia, PrimeVue, Tailwind CSS 4 |
| Data | MySQL 8, Redis 7 |
| Runtime | Docker (PHP-FPM + Nginx), optional XAMPP for local PHP |

## Requirements

**Docker (recommended)**

- Docker Desktop / Docker Engine with Compose v2

**Local (without Docker)**

- PHP 8.2+, Composer, Node.js 22+, MySQL, Redis (optional)

## Quick start (Docker development)

```bash
cp .env.docker.example .env
docker compose up -d --build
docker compose exec app php artisan migrate
```

Then open:

| Service | URL |
| --- | --- |
| Application | http://localhost:8080 |
| Vite (HMR) | http://localhost:5173 |
| Mailpit | http://localhost:8025 |

### Development services

`docker compose up` starts:

- **app** — PHP 8.2-FPM
- **nginx** — HTTP front (port `8080`)
- **mysql** — MySQL 8
- **redis** — cache / session / queue
- **node** — Vite dev server
- **mailpit** — local SMTP + UI

Optional queue worker and scheduler:

```bash
docker compose --profile queue up -d
```

### Useful commands

```bash
docker compose exec app php artisan tinker
docker compose exec app composer install
docker compose exec app php artisan test
docker compose logs -f app nginx
docker compose down
```

## Production deployment (Docker)

1. Create a production `.env` (start from `.env.docker.example`):

```bash
cp .env.docker.example .env
```

Set at least:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY=` (run `php artisan key:generate` after first start if empty)
- `APP_URL=` your public URL
- Strong `DB_*` / `MYSQL_*` passwords
- Real mail / FTP / AWS credentials as needed

2. Build and start:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

3. Migrate:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

### Production services

| Service | Role |
| --- | --- |
| **app** | PHP-FPM (assets built into the image) |
| **nginx** | Serves `public/` and proxies PHP |
| **mysql** | Database (persistent volume) |
| **redis** | Cache, sessions, queues |
| **queue** | `queue:work` on Redis |
| **scheduler** | Runs `schedule:run` every minute |

Frontend assets are compiled inside the production image — no Node/Vite container at runtime. Uploads and logs persist via the `storage-data` volume.

## Environment files

| File | Use |
| --- | --- |
| `.env.docker.example` | Docker defaults (`DB_HOST=mysql`, Redis, Mailpit, `APP_URL=http://localhost:8080`) |
| `.env.example` | Non-Docker / XAMPP defaults (`DB_HOST=127.0.0.1`) |

Never commit `.env`. Optional Compose port overrides:

```env
APP_PORT=8080
VITE_PORT=5173
FORWARD_DB_PORT=3306
FORWARD_REDIS_PORT=6379
FORWARD_MAIL_PORT=1025
FORWARD_MAIL_DASHBOARD_PORT=8025
```

## Local setup (without Docker)

```bash
cp .env.example .env
composer install
php artisan key:generate
# configure DB_* in .env, then:
php artisan migrate
npm install
npm run dev
```

Serve with `php artisan serve` or your local Apache/Nginx (e.g. XAMPP) pointing at `public/`.

## Project layout

```
app/                 Laravel application code
resources/js/        Vue SPA (pages, components, composables)
routes/              web + API routes
docker/              PHP/Nginx images and configs
Dockerfile           Multi-stage production image
docker-compose.yml   Development stack
docker-compose.prod.yml
```

More Docker detail: [docker/README.md](docker/README.md).

## License

MIT (Laravel framework portions as upstream).
