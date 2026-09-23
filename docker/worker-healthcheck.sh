#!/usr/bin/env sh
set -eu

config=/etc/supervisord-worker.conf

/usr/bin/supervisorctl -c "$config" status queue-worker | grep -q 'RUNNING'

if [ "${RUN_SCHEDULER:-true}" = "true" ]; then
    /usr/bin/supervisorctl -c "$config" status scheduler | grep -q 'RUNNING'
fi
