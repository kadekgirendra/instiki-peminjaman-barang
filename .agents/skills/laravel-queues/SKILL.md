---
name: laravel-queues
description: >-
  Create, dispatch, configure, and test Laravel queue jobs on Laravel 11–13.
  Covers Redis queue driver, job classes, middleware, batching, workers,
  failed jobs, Supervisor, and testing. Version-aware — tags features by
  Laravel major version and avoids APIs unavailable in the project.
  Complements laravel-horizon for Horizon dashboard/workers.
  Use when working with queues, jobs, ShouldQueue, dispatch(), queue:work,
  job batches, failed_jobs, or config/queue.php on Laravel 11, 12, or 13.
---

# Laravel Queues (11–13)

Laravel's queue system offloads slow work to background workers. This skill covers the **framework queue API** with **Redis** as the primary driver.

**Horizon is separate.** For Redis dashboard, supervisors, and `php artisan horizon`, use the `laravel-horizon` skill. Horizon only manages Redis queues — run `queue:work` alongside Horizon when using failover with a `database` connection.

## Version detection (required first step)

Before suggesting code or config, detect the project's Laravel major version:

```bash
composer show laravel/framework | grep versions
# or read composer.json: "laravel/framework": "^12.0"
```

| Detected version | Apply |
|------------------|-------|
| **11.x** | Only features tagged `[11+]`; skip `[12+]` and `[13+]` |
| **12.x** | Features tagged `[11+]` and `[12+]`; skip `[13+]` |
| **13.x** | All tagged features |
| **14+** (future) | Extend [version-matrix.md](references/version-matrix.md) from official docs before using new APIs |

**Never** use `DebounceFor`, `queue:pause`, failover driver, or other version-gated APIs without confirming the project version. When unsure, state the minimum version required.

Full feature matrix: [version-matrix.md](references/version-matrix.md)

## Prerequisites (Redis)

- `QUEUE_CONNECTION=redis` in `.env`
- Redis connection in `config/database.php`
- `config/queue.php` — `redis` connection with `retry_after`, optional `block_for`
- Workers: `php artisan queue:work redis` or Horizon (see Horizon skill)

```php
// config/queue.php — typical Redis connection
'redis' => [
    'driver' => 'redis',
    'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
    'block_for' => null,
    'after_commit' => false,
],
```

**Redis cluster:** queue names must include a [hash tag](https://redis.io/docs/latest/develop/using-commands/keyspace/#hashtags), e.g. `'{default}'`.

**Worker timeout rule:** `--timeout` must be **several seconds less** than `retry_after` to prevent duplicate processing.

## Quick start

```bash
php artisan make:job ProcessPodcast
php artisan queue:work redis --queue=high,default --tries=3
php artisan queue:restart   # after deploy
```

```php
use App\Jobs\ProcessPodcast;

ProcessPodcast::dispatch($podcast);
ProcessPodcast::dispatch($podcast)->onQueue('high')->delay(now()->addMinutes(10));
```

## Conventions

| Item | Location / pattern |
|------|-------------------|
| Jobs | `app/Jobs/` — implement `ShouldQueue`, use `Queueable` trait |
| Middleware | `app/Jobs/Middleware/` — return from `middleware()` on job |
| Config | `config/queue.php` — `connections`, `batching`, `failed` |
| Batches table | `job_batches` migration (included in default Laravel install) |
| Failed jobs | `failed_jobs` table or `make:queue-failed-table` |
| Workers (prod) | Supervisor → `queue:work`; Redis at scale → Horizon |
| Deploy | `php artisan queue:restart` (or `horizon:terminate` with Horizon) |

## Core patterns

### Job class

```php
namespace App\Jobs;

use App\Models\Podcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPodcast implements ShouldQueue
{
    use Queueable;

    public function __construct(public Podcast $podcast) {}

    public function handle(): void
    {
        // Eloquent models serialize as IDs and are re-hydrated on handle
    }
}
```

- Use `withoutRelations()` or `#[WithoutRelations]` `[12+]` to shrink payloads
- Binary data: `base64_encode` before queueing
- Unique jobs: `ShouldBeUnique` + `uniqueId()` `[11+]`; `#[UniqueFor]` / `ShouldBeUniqueUntilProcessing` `[12+]`
- Debounced jobs: `#[DebounceFor]` + `debounceId()` `[13+]` only — mutually exclusive with `ShouldBeUnique`

### Dispatching

```php
ProcessPodcast::dispatch($podcast);
ProcessPodcast::dispatch($podcast)->afterCommit();           // after DB commit
Bus::batch([new A, new B])->then(...)->dispatch();          // batching [11+]
ProcessPodcast::dispatch($podcast)->onConnection('redis')->onQueue('emails');
```

`after_commit` on the connection or per-dispatch prevents jobs running before committed transactions.

### Job middleware (common)

```php
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Queue\Middleware\ThrottlesExceptions;

public function middleware(): array
{
    return [
        new RateLimited('key'),
        (new WithoutOverlapping($this->order->id))->releaseAfter(60),
        (new ThrottlesExceptions(10, 5 * 60))->backoff(60),
    ];
}
```

See [job-middleware.md](references/job-middleware.md) for `Skip`, `SkipIf`, releasing jobs `[13+]`, and Redis-specific limiters.

### Retries and timeouts

Set on the job class, at dispatch, or on the worker:

```php
public int $tries = 3;
public int $timeout = 120;
public int $backoff = 60;           // or backoff(): array [12+]
public int $maxExceptions = 3;      // [12+]
```

```shell
php artisan queue:work redis --tries=3 --timeout=90 --max-jobs=1000 --max-time=3600
```

### Failed jobs

```shell
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
php artisan queue:prune-failed --hours=48
```

Implement `failed(?Throwable $e)` on the job for cleanup. See [failed-jobs.md](references/failed-jobs.md).

## Horizon complement

| Task | Use |
|------|-----|
| Redis worker pool, balancing, metrics dashboard | `laravel-horizon` |
| Create/dispatch jobs, middleware, batches, testing | This skill (`laravel-queues`) |
| Deploy with Horizon | `horizon:terminate` + process monitor |
| Deploy with `queue:work` only | `queue:restart` + Supervisor |
| Failover including `database` connection `[12+]` | Horizon for `redis` + separate `queue:work database` |

## Lookup tables

| Task | Reference |
|------|-----------|
| Version-gated features | [version-matrix.md](references/version-matrix.md) |
| Job types, unique, debounced, encrypted | [creating-jobs.md](references/creating-jobs.md) |
| Rate limiting, overlaps, throttling, skip | [job-middleware.md](references/job-middleware.md) |
| Dispatch, chains, failover, FIFO `[12+]` | [dispatching-jobs.md](references/dispatching-jobs.md) |
| Batches | [job-batching.md](references/job-batching.md) |
| Workers, Supervisor, pause `[12+]` | [running-workers.md](references/running-workers.md) |
| Failed jobs, pruning, events | [failed-jobs.md](references/failed-jobs.md) |
| Bus::fake, chain/batch tests | [testing.md](references/testing.md) |
| Artisan commands | [artisan-commands.md](references/artisan-commands.md) |

## Common gotchas

- **Connections vs queues** — connection = backend (redis); queue = named stack on that connection (`default`, `high`)
- **Long-lived workers** — code changes require `queue:restart` or worker recycle; static state persists between jobs
- **`retry_after` vs `--timeout`** — timeout must be shorter than `retry_after`
- **`block_for=0`** on Redis — blocks indefinitely; delays signal handling
- **Unique/debounce locks** — require a shared cache (Redis) across all app servers
- **Unique jobs** — do not apply inside batches
- **Debounced `[13+]`** — cannot combine with `ShouldBeUnique`
- **Horizon + failover `[12+]`** — run workers for non-Redis failover connections separately
- **Maintenance mode** — workers skip jobs unless `--force`

## Extending for new Laravel versions

When Laravel 14+ ships:

1. Read the new `queues.md` from [laravel/docs](https://github.com/laravel/docs)
2. Add rows to [version-matrix.md](references/version-matrix.md) with `[14+]` tags
3. Update reference files for changed APIs only
4. Add a row to the version table in this file

## Source documentation

| Version | Queues doc |
|---------|------------|
| 11.x | https://github.com/laravel/docs/blob/11.x/queues.md |
| 12.x | https://github.com/laravel/docs/blob/12.x/queues.md |
| 13.x | https://github.com/laravel/docs/blob/13.x/queues.md |
