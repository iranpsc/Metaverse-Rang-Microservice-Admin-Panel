# Admin Hamgit

Laravel 12 + Vue 3 admin panel for Hamgit. The backend exposes a JSON API; the frontend is a Vite-powered SPA served from Laravel.

## Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.2, Laravel 12, Sanctum, Spatie Permission / Activity Log |
| Frontend | Vue 3, Vite 8, Pinia, PrimeVue, Tailwind CSS 4 |
| Data | MySQL 8, Redis 7 |
| Runtime | Docker (PHP + MySQL + Redis), optional XAMPP for local PHP |

## Requirements

**Docker (recommended)**

- Docker Desktop / Docker Engine with Compose v2

**Local (without Docker)**

- PHP 8.2+, Composer, Node.js 22+, MySQL, Redis (optional)

## Quick start (Docker)

**Production / Dockploy** (app baked into the image — no host bind mount):

```bash
cp .env.example .env   # or inject env via your platform
docker compose up -d --build
docker compose exec app php artisan migrate --force
```

**Local development** (bind-mount source + asset build):

```bash
cp .env.example .env
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec app php artisan migrate
```

Then open the app at `http://localhost:${APP_PORT:-8088}`.

See [docker/README.md](docker/README.md) for volumes, queue profile, and more commands.

Optional queue worker and scheduler:

```bash
docker compose --profile queue up -d
```

### Useful commands

```bash
docker compose exec app php artisan tinker
docker compose exec app composer install
docker compose exec app php artisan test
docker compose logs -f app
docker compose down
```

## Environment files

Copy `.env.example` to `.env` for Docker development (defaults use `DB_HOST=mysql`, Redis, Mailpit, `APP_URL=http://localhost:8080`). For non-Docker / XAMPP, set `DB_HOST=127.0.0.1` and adjust credentials as needed.

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
# set DB_HOST=127.0.0.1 and local DB credentials
composer install
php artisan key:generate
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
docker/              PHP Docker image and configs
docker-compose.yml   Local development stack
```

More Docker detail: [docker/README.md](docker/README.md).

## License

MIT (Laravel framework portions as upstream).
