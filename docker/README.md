# Docker

## Development

1. Copy environment file and adjust values if needed:

```bash
cp .env.docker.example .env
```

2. Start the stack:

```bash
docker compose up -d --build
```

3. Run migrations (first time or after schema changes):

```bash
docker compose exec app php artisan migrate
```

4. Open the app:

- Application: http://localhost:8080
- Vite dev server: http://localhost:5173
- Mailpit UI: http://localhost:8025

### Optional services

Start queue worker and scheduler:

```bash
docker compose --profile queue up -d
```

### Common commands

```bash
docker compose exec app php artisan tinker
docker compose exec app composer install
docker compose exec app php artisan test
docker compose logs -f app
docker compose down
```

## Production

1. Create a production `.env` with real credentials (`APP_ENV=production`, `APP_DEBUG=false`, etc.).

2. Build and start:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

3. Run migrations:

```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

The production compose file builds frontend assets inside the image, runs PHP-FPM + Nginx, MySQL, Redis, and a queue worker. No Node/Vite container is needed in production.
