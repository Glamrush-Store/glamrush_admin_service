#!/usr/bin/env sh
set -eu

config=/etc/supervisord-worker.conf
if [ "${RUN_SCHEDULER:-true}" = "true" ]; then
    config=/etc/supervisord-with-scheduler.conf
fi

exec /usr/bin/supervisord -c "$config"
