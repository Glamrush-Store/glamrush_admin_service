#!/usr/bin/env bash
set -euo pipefail

pids=""

shutdown() {
    echo "Stopping worker processes..."

    for pid in $pids; do
        if kill -0 "$pid" 2>/dev/null; then
            kill -TERM "$pid" 2>/dev/null || true
        fi
    done

    wait || true
}

trap shutdown INT TERM

if [ "${RUN_SCHEDULER:-true}" = "true" ]; then
    php artisan schedule:work &
    pids="$pids $!"
fi

php artisan queue:work "${QUEUE_CONNECTION:-redis}" \
    --queue="${QUEUE_NAME:-default}" \
    --sleep="${QUEUE_SLEEP:-3}" \
    --tries="${QUEUE_TRIES:-3}" \
    --timeout="${QUEUE_TIMEOUT:-90}" \
    --max-time="${QUEUE_MAX_TIME:-3600}" &
pids="$pids $!"

while true; do
    for pid in $pids; do
        if ! kill -0 "$pid" 2>/dev/null; then
            shutdown
            exit 1
        fi
    done

    sleep 2
done