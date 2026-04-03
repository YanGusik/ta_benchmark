#!/bin/bash
set -euo pipefail
cd "$(dirname "$0")"

DURATION=${1:-30}
CONNECTIONS=1000
THREADS=4
TRUEASYNC_PORT=8083
OCTANE_PORT=8084

echo "============================================="
echo " Benchmark: TrueAsync vs Octane Swoole"
echo " Duration: ${DURATION}s | Connections: ${CONNECTIONS}"
echo " Workers: 4 each | Queries: 10 SQL per request"
echo "============================================="
echo ""

# --- Start services ---
echo "--- Starting TrueAsync ---"
(cd trueasync && docker compose up -d 2>&1) | tail -3
echo ""
echo "--- Starting Octane Swoole ---"
(cd octane_swoole && docker compose up -d 2>&1) | tail -3
echo ""

# --- Wait for services ---
wait_for() {
    local name=$1 url=$2 max=60 i=0
    printf "Waiting for %s..." "$name"
    while ! curl -sf -o /dev/null "$url" 2>/dev/null; do
        i=$((i + 1))
        [ $i -ge $max ] && echo " FAILED" && return 1
        sleep 1; printf "."
    done
    echo " OK"
}

wait_for "TrueAsync DB" "http://localhost:${TRUEASYNC_PORT}/hello"
wait_for "Octane DB"     "http://localhost:${OCTANE_PORT}/hello"

# --- Seed databases ---
echo ""
echo "--- Seeding databases ---"
(cd trueasync && docker compose exec -T app php artisan migrate:fresh --seed --force 2>&1) | tail -3
(cd octane_swoole && docker compose exec -T app php artisan migrate:fresh --seed --force 2>&1) | tail -3
echo ""

# --- Verify endpoints ---
echo "--- Verifying /bench endpoints ---"
echo "TrueAsync:"
curl -s "http://localhost:${TRUEASYNC_PORT}/bench" | python3 -m json.tool 2>/dev/null || curl -s "http://localhost:${TRUEASYNC_PORT}/bench"
echo ""
echo "Octane:"
curl -s "http://localhost:${OCTANE_PORT}/bench" | python3 -m json.tool 2>/dev/null || curl -s "http://localhost:${OCTANE_PORT}/bench"
echo ""

# === WARMUP ===
echo "--- Warmup Phase 1: Priming (10 sequential requests each) ---"
for i in $(seq 1 10); do
    curl -s -o /dev/null "http://localhost:${TRUEASYNC_PORT}/bench"
    curl -s -o /dev/null "http://localhost:${OCTANE_PORT}/bench"
done
echo "Done"

echo "--- Warmup Phase 2: Light load (10s, 50 connections) ---"
wrk -t2 -c50 -d10s "http://localhost:${TRUEASYNC_PORT}/bench" > /dev/null 2>&1
wrk -t2 -c50 -d10s "http://localhost:${OCTANE_PORT}/bench" > /dev/null 2>&1
echo "Done"

echo "--- Warmup Phase 3: Medium load (10s, 200 connections) ---"
wrk -t${THREADS} -c200 -d10s "http://localhost:${TRUEASYNC_PORT}/bench" > /dev/null 2>&1
wrk -t${THREADS} -c200 -d10s "http://localhost:${OCTANE_PORT}/bench" > /dev/null 2>&1
echo "Done"
echo ""

sleep 3

# === BENCHMARK ===
echo "============================================="
echo " [1/2] TrueAsync FrankenPHP (4 async workers)"
echo "============================================="
wrk -t${THREADS} -c${CONNECTIONS} -d${DURATION}s --latency "http://localhost:${TRUEASYNC_PORT}/bench"

echo ""
sleep 5

echo "============================================="
echo " [2/2] Octane Swoole (4 blocking workers)"
echo "============================================="
wrk -t${THREADS} -c${CONNECTIONS} -d${DURATION}s --latency "http://localhost:${OCTANE_PORT}/bench"

echo ""
echo "============================================="
echo " Benchmark complete"
echo "============================================="
