# TrueAsync FrankenPHP

Laravel 13 + TrueAsync (coroutine-based async I/O) on FrankenPHP.

## Requirements

- Docker & Docker Compose

## Quick Start

```bash
# Build (first time takes ~20 min — compiles PHP + FrankenPHP from source)
docker compose build

# Start
docker compose up -d

# Run migrations and seed
docker compose exec app php artisan migrate:fresh --seed --force

# Verify
curl http://localhost:8083/bench
```

## Configuration

Edit `compose.yml` to change workers/buffer:

```yaml
command: php artisan async:franken --host=0.0.0.0 --port=8080 --workers=4 --buffer=50
```

- `--workers` — number of PHP worker threads
- `--buffer` — max concurrent coroutines per worker (effective concurrency = workers x buffer)

## Ports

| Service | Port |
|---------|------|
| App | 8083 |
| PostgreSQL | 5434 |

## Stack

- PHP 8.6-dev (ZTS, TrueAsync ABI v0.9.0)
- FrankenPHP v2.11.2 (true-async fork)
- Laravel 13.2
- PostgreSQL 16
- OPcache enabled
