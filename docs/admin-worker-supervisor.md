# Admin worker process supervision

The admin worker image now uses Supervisor, like the storefront backend worker. Supervisor remains the container's foreground process and independently restarts the Laravel queue worker after planned `--max-time` exits, job timeouts, crashes, or `queue:restart` signals. The scheduler is a separate supervised process when enabled.

## Configuration

The Docker image keeps its existing admin worker environment variables:

| Variable | Default | Purpose |
| --- | --- | --- |
| `QUEUE_CONNECTION` | `redis` | Laravel queue connection passed to `queue:work` |
| `QUEUE_NAME` | `default` | Queue name |
| `QUEUE_SLEEP` | `3` | Idle polling sleep, seconds |
| `QUEUE_TRIES` | `3` | Job attempts |
| `QUEUE_TIMEOUT` | `60` | Job timeout, seconds |
| `QUEUE_MAX_TIME` | `3600` | Planned worker rotation, seconds |
| `QUEUE_BACKOFF` | `5` | Retry backoff, seconds |
| `QUEUE_MEMORY` | `256` | Worker memory limit, MB |
| `RUN_SCHEDULER` | `true` | Start and monitor `schedule:work` |

The default timeout is below the Redis `retry_after` default of 90 seconds. If deployment overrides either value, keep `QUEUE_TIMEOUT` several seconds lower than `REDIS_QUEUE_RETRY_AFTER` to avoid duplicate processing.

`RUN_SCHEDULER=false` selects a queue-only Supervisor configuration. The health check always requires `queue-worker` to be RUNNING and requires `scheduler` only when scheduling is enabled. The health check is intentionally process-level; it does not prove that Redis or application jobs are healthy.

The existing worker entrypoint still supports `RUN_MIGRATIONS` and `CACHE_CONFIG`. This change does not alter queue connection settings, job classes, migrations, or the storefront backend worker.

## Deployment verification

Build and deploy the updated `Dockerfile.worker` image. Check `supervisorctl -c /etc/supervisord-worker.conf status` inside the container: `queue-worker` and, by default, `scheduler` should show RUNNING. After `QUEUE_MAX_TIME` seconds, the queue-worker PID should change while the container remains running and healthy. If `RUN_SCHEDULER=false`, only the queue-worker should appear.

Inspect the container's stdout/stderr logs for repeated rapid restarts. Supervisor keeps the container alive across individual child exits, but a continuously failing child should still be investigated; the health check will eventually mark the container unhealthy.
