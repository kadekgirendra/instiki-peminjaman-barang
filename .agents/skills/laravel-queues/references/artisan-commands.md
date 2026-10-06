# Artisan Commands — Queues

## Workers

| Command | Version | Purpose |
|---------|---------|---------|
| `queue:work {connection}` | 11+ | Start worker |
| `queue:listen {connection}` | 11+ | Worker with auto code reload |
| `queue:restart` | 11+ | Graceful restart signal (after deploy) |
| `queue:pause {connection:queue}` | 12+ | Pause queue processing |
| `queue:continue {connection:queue}` | 12+ | Resume paused queue |

### `queue:work` options (common)

```shell
php artisan queue:work redis --queue=high,default --tries=3 --timeout=90
php artisan queue:work --once --stop-when-empty --max-jobs=1000 --max-time=3600
php artisan queue:work --sleep=3 --force -v
```

## Failed jobs

| Command | Version | Purpose |
|---------|---------|---------|
| `queue:failed` | 11+ | List failed jobs |
| `queue:retry {id}` / `all` | 11+ | Retry failed jobs |
| `queue:forget {id}` | 11+ | Delete one failed record |
| `queue:flush` | 11+ | Delete all failed records |
| `queue:prune-failed --hours=48` | 11+ | Prune old failures |

## Batches

| Command | Version | Purpose |
|---------|---------|---------|
| `queue:prune-batches --hours=48` | 11+ | Prune finished batch records |

## Queue management

| Command | Version | Purpose |
|---------|---------|---------|
| `queue:clear {connection}` | 11+ | Clear pending jobs (`--queue=name`) |
| `queue:monitor redis:default --max=100` | 11+ | Monitor queue size thresholds |
| `queue:forget` | 11+ | (see failed jobs) |

## Scaffolding

| Command | Version | Purpose |
|---------|---------|---------|
| `make:job {Name}` | 11+ | Create job class |
| `make:job-middleware {Name}` | 12+ | Create job middleware |
| `make:queue-table` | 11+ | Jobs table migration |
| `make:queue-failed-table` | 11+ | Failed jobs table migration |
| `make:queue-batches-table` | 11+ | Batches table (if missing) |

## Horizon (Redis dashboard — separate skill)

| Command | Purpose |
|---------|---------|
| `horizon` | Start Horizon workers |
| `horizon:terminate` | Graceful shutdown (deploy) |
| `horizon:pause` / `horizon:continue` | Pause/resume Horizon |
| `horizon:status` | Worker status |
| `horizon:clear` | Clear pending Redis jobs |

See `laravel-horizon` for full Horizon command reference.
