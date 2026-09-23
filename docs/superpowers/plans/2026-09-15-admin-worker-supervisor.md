# Admin Worker Supervisor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Keep the admin queue worker and optional scheduler running across planned Laravel queue-worker exits without restarting the whole container.

**Architecture:** Replace the shell child-PID watchdog with Supervisor, matching the backend worker's `autostart` and `autorestart` behavior. A shared queue config and optional scheduler config preserve `RUN_SCHEDULER=false`, and a health check verifies exactly the enabled processes.

**Tech Stack:** PHP 8.3 CLI Docker image, Debian Supervisor, Laravel 12 queue and scheduler, POSIX shell.

**Spec:** User request in this task: "make the admin worker config similar to backend worker"; reference `C:\Users\USER\PhpstormProjects\glamrush_backend_service\docker\supervisord-worker.conf`.

## Global Constraints

- Keep admin-specific environment names `QUEUE_CONNECTION`, `QUEUE_NAME`, `QUEUE_SLEEP`, `QUEUE_TRIES`, `QUEUE_TIMEOUT`, `QUEUE_MAX_TIME`, and `RUN_SCHEDULER`.
- Keep `RUN_SCHEDULER=false` disabling scheduler process and scheduler health-check requirement.
- Preserve worker entrypoint caching and optional migration behavior.
- Do not modify the customer-facing backend repository.
- Keep worker timeout below Redis `retry_after` default of 90 seconds; set default `QUEUE_TIMEOUT=60`.

---

### Task 1: Supervisor Process Configuration

**Files:**
- Create: `docker/supervisord-worker.conf` (socket, Supervisor settings, queue program)
- Create: `docker/supervisord-scheduler.conf` (scheduler program)
- Create: `docker/supervisord-with-scheduler.conf` (includes both program files)
- Modify: `docker/worker-run.sh` (selects configuration and `exec`s Supervisor)

**Interfaces:**
- Consumes existing Docker env names.
- Produces `queue-worker` always and `scheduler` only when `RUN_SCHEDULER=true`.

- [x] **Step 1: Write a pre-change static assertion** that `docker/worker-run.sh` still contains `exit 1` on child departure and lacks `supervisord`.
- [x] **Step 2: Run the assertion** and confirm it fails the target condition.
- [x] **Step 3: Add the queue Supervisor program** with `command=/bin/sh -lc "exec php artisan queue:work %(ENV_QUEUE_CONNECTION)s --queue=%(ENV_QUEUE_NAME)s --sleep=%(ENV_QUEUE_SLEEP)s --tries=%(ENV_QUEUE_TRIES)s --backoff=%(ENV_QUEUE_BACKOFF)s --timeout=%(ENV_QUEUE_TIMEOUT)s --max-time=%(ENV_QUEUE_MAX_TIME)s --memory=%(ENV_QUEUE_MEMORY)s --no-interaction"`, `autostart=true`, `autorestart=true`, TERM group shutdown, and stdout/stderr to Docker streams.
- [x] **Step 4: Add the scheduler program** as `php artisan schedule:work --verbose --no-interaction` with `autorestart=true`; use the documented Supervisor `[include] files = /etc/supervisord-worker.conf /etc/supervisord-scheduler.conf` for enabled mode.
- [x] **Step 5: Replace the watchdog** with a shell script that chooses `/etc/supervisord-with-scheduler.conf` for `RUN_SCHEDULER=true`, otherwise `/etc/supervisord-worker.conf`, then `exec /usr/bin/supervisord -c "$config"`.
- [x] **Step 6: Re-run static assertions** and shell syntax checks; confirm no PID polling remains.

### Task 2: Image and Health Check

**Files:**
- Modify: `Dockerfile.worker`
- Create: `docker/worker-healthcheck.sh`

**Interfaces:**
- Consumes Supervisor socket from `docker/supervisord-worker.conf` and `RUN_SCHEDULER`.
- Produces Docker `HEALTHCHECK` status 0 when queue is RUNNING and, if enabled, scheduler is RUNNING.

- [x] **Step 1: Write static assertions** that Dockerfile currently has no `supervisor` package and no worker `HEALTHCHECK`.
- [x] **Step 2: Run assertions** to observe expected failures.
- [x] **Step 3: Install Debian `supervisor`**, copy the three config files into `/etc`, mark health-check script executable, and add `HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 CMD ["docker/worker-healthcheck.sh"]`.
- [x] **Step 4: Add health-check code** using `/usr/bin/supervisorctl -c /etc/supervisord-worker.conf status queue-worker | grep -q 'RUNNING'` and the equivalent scheduler check only when enabled.
- [x] **Step 5: Re-run static assertions** and confirm the worker image still uses the existing entrypoint and launcher.

### Task 3: Operational Verification and Documentation

**Files:**
- Create: `docs/admin-worker-supervisor.md`
- Test: `docker/worker-run.sh`, `docker/worker-healthcheck.sh`, `Dockerfile.worker`, and Supervisor configs.

**Interfaces:**
- Documents the existing admin environment variables and expected one-hour queue rotation.

- [x] **Step 1: Document** `QUEUE_MAX_TIME=3600`, `QUEUE_TIMEOUT=60`, `QUEUE_BACKOFF=5`, `QUEUE_MEMORY=256`, optional `RUN_SCHEDULER`, and how Supervisor restarts planned exits.
- [x] **Step 2: Run shell syntax checks** with an available Bash implementation and check config names/interpolation and `git diff --check`.
- [x] **Step 3: Check Docker availability**; the local daemon is unavailable, so runtime validation requires deployment or an accessible Docker daemon.
- [x] **Step 4: Verify** the repository status contains only the intended worker/docs changes, then report validation limitations.

## Self-Review

- Covers planned queue exit, scheduler independence, optional scheduler behavior, health checks, timeout/retry consistency, and Docker installation.
- The queue program uses the existing admin env names; newly added backoff and memory names are defined in the Dockerfile.
- No backend-service files are changed.
