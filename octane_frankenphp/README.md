# Octane FrankenPHP

Laravel 13 + Octane with official FrankenPHP (blocking mode).

## Requirements

- Docker & Docker Compose

## Quick Start

```bash
# Build
docker compose build

# Start
docker compose up -d

# Install FrankenPHP binary (first time only)
docker compose exec app php artisan octane:install --server=frankenphp

# Run migrations and seed
docker compose exec app php artisan migrate:fresh --seed --force

# Verify
curl http://localhost:8085/bench
```

## Configuration

Edit `compose.yml` to change workers:

```yaml
command: php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8080 --workers=4 --max-requests=0
```

## Ports

| Service | Port |
|---------|------|
| App | 8085 |
| PostgreSQL | 5436 |

## Stack

- PHP 8.5.4 (NTS)
- FrankenPHP (official, downloaded by Octane)
- Laravel Octane 2.x
- Laravel 13.2
- PostgreSQL 16
