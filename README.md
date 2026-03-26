# ta_benchmark

Benchmark comparing different Laravel server adapters under load using [k6](https://k6.io).

## Adapters

| Adapter | Port | Workers | Image |
|---|---|---|---|
| PHP-FPM + nginx | 8082 | 12 | `php:8.4-fpm-alpine` + `nginx:alpine` |
| Octane Swoole | 8084 | 12 | `phpswoole/swoole:5.1-php8.3-alpine` |
| TrueAsync FrankenPHP | 8083 | WIP | `trueasync/php-true-async:latest-frankenphp` |

## Routes tested

- `GET /hello` — pure JSON response, no DB
- `GET /test` — one DB query (`SELECT pg_sleep(0.01)`, simulates 10ms DB work)

---

## Setup

### Requirements

- Docker + Docker Compose
- [k6](https://k6.io/docs/get-started/installation/)

### PHP-FPM

```bash
cd fpm
docker compose up -d --build
docker compose exec php chmod -R 777 /app/storage /app/bootstrap/cache
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
curl http://localhost:8082/hello
```

### Octane Swoole

```bash
cd octane_swoole
docker compose up -d --build
docker compose exec app chmod -R 777 /app/storage /app/bootstrap/cache
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
curl http://localhost:8084/hello
```

### TrueAsync

> ⚠️ Currently unstable — pending bug fix in TrueAsync fork.

```bash
cd trueasync
docker compose up -d
docker compose exec app chmod -R 777 /app/storage /app/bootstrap/cache
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose restart app
curl http://localhost:8083/hello
```

---

## Running benchmarks

```bash
# PHP-FPM
k6 run k6/fpm.js

# Octane Swoole
k6 run k6/octane_swoole.js

# TrueAsync (when stable)
k6 run k6/trueasync.js
```

---

## Results

Load: 350 req/s `/hello` + 150 req/s `/test` = **500 req/s total** via k6 `constant-arrival-rate`, 30s duration.

### PHP-FPM (12 workers) vs Octane Swoole (12 workers)

| Metric | PHP-FPM (12w) | Octane Swoole (12w) |
|---|---|---|
| Target rate | 500 req/s | 500 req/s |
| Actual throughput | 200 req/s | **500 req/s** |
| Dropped iterations | 7,937 | **7** |
| avg latency | 4,000ms | **30ms** |
| median latency | 4,570ms | **19ms** |
| p(90) latency | 4,920ms | **47ms** |
| p(95) latency | 4,970ms | **112ms** |
| Failed requests | 0% | 0% |

> PHP-FPM collapses under 500 req/s even with 12 workers — requests pile up in queue causing 4+ second latency. Octane Swoole handles the full load with zero errors.

### TrueAsync FrankenPHP — preliminary result (1 worker, buffer=1)

Tested separately at **300 req/s** (multi-worker mode temporarily unavailable):

| Metric | TrueAsync (1 worker) | PHP-FPM (12w) @ 300 req/s |
|---|---|---|
| Actual throughput | **300 req/s** | 200 req/s |
| avg latency | **5.52ms** | ~4,000ms |
| p(95) latency | **14.68ms** | ~5,000ms |
| Dropped iterations | **0** | 7,937 |

> 1 TrueAsync worker outperforms 12 FPM workers. Full multi-worker benchmark pending bug fix.

---

## Known issues

- **TrueAsync**: `Async\AsyncException: Failed to monitor process` when using `--workers > 1` or `--buffer > 1` — bug in TrueAsync fork, pending fix from [@EdmondDantes](https://github.com/EdmondDantes)

---

## Notes

- Each adapter has its own PostgreSQL instance on a different port to avoid conflicts
- `APP_DEBUG=false` in all setups for fair comparison
- OPcache enabled in PHP-FPM
- Swoole workers = PHP processes (not coroutines)
