# Octane Swoole

Laravel 13 + Octane with Swoole 6.2 (ZTS, thread mode).

## Requirements

- Docker & Docker Compose

## Quick Start

```bash
# Build
docker compose build

# Start
docker compose up -d

# Run migrations and seed
docker compose exec app php artisan migrate:fresh --seed --force

# Verify
curl http://localhost:8084/bench
```

## Configuration

Edit `compose.yml` to change workers:

```yaml
command: php artisan octane:start --server=swoole --host=0.0.0.0 --port=8080 --workers=4
```

## Ports

| Service | Port |
|---------|------|
| App | 8084 |
| PostgreSQL | 5435 |

## Stack

- PHP 8.5.4 (ZTS)
- Swoole 6.2.0 (thread mode)
- Laravel Octane 2.x
- Laravel 13.2
- PostgreSQL 16
- OPcache enabled
