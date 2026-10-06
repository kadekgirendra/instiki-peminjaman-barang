# Version Matrix — Laravel Queues

Tags: `[11+]` available in Laravel 11 and later · `[12+]` requires 12+ · `[13+]` requires 13+

**Agent rule:** Detect `laravel/framework` major version first. Only recommend features at or below that version.

## Feature availability

| Feature | 11 | 12 | 13 | Notes |
|---------|:--:|:--:|:--:|-------|
| `ShouldQueue`, `Queueable`, `dispatch()` | ✓ | ✓ | ✓ | Core API |
| Job middleware (RateLimited, WithoutOverlapping, etc.) | ✓ | ✓ | ✓ | |
| `ShouldBeUnique`, `uniqueId()` | ✓ | ✓ | ✓ | Requires cache locks |
| `ShouldBeEncrypted` | ✓ | ✓ | ✓ | |
| Job batching (`Bus::batch`) | ✓ | ✓ | ✓ | |
| DynamoDB batch/failed storage | ✓ | ✓ | ✓ | Optional AWS setup |
| `afterCommit()` / `after_commit` config | ✓ | ✓ | ✓ | |
| Job chains | ✓ | ✓ | ✓ | |
| `queue:restart`, Supervisor deployment | ✓ | ✓ | ✓ | |
| Testing (`Bus::fake`, chain/batch assertions) | ✓ | ✓ | ✓ | |
| `#[WithoutRelations]` attribute | — | ✓ | ✓ | `[12+]` |
| `#[UniqueFor]`, `ShouldBeUniqueUntilProcessing`, `uniqueVia()` | — | ✓ | ✓ | `[12+]` |
| `$backoff` property / `backoff()` method | — | ✓ | ✓ | `[12+]` |
| `$maxExceptions`, time-based attempts | — | ✓ | ✓ | `[12+]` |
| `fail()` on specific exceptions | — | ✓ | ✓ | `[12+]` |
| `deferred` connection (post-response sync) | — | ✓ | ✓ | `[12+]` |
| `background` connection (spawned PHP process) | — | ✓ | ✓ | `[12+]` |
| Queue **failover** driver | — | ✓ | ✓ | `[12+]` — `QUEUE_CONNECTION=failover` |
| SQS **FIFO** queues (`onGroup`, `deduplicationId`) | — | ✓ | ✓ | `[12+]` — not Redis |
| `queue:pause` / `queue:continue` | — | ✓ | ✓ | `[12+]` |
| `Queue::withoutInterruptionPolling()` | — | ✓ | ✓ | `[12+]` |
| `make:job-middleware` Artisan command | — | ✓ | ✓ | `[12+]` |
| **Debounced jobs** (`#[DebounceFor]`, `debounceId()`) | — | — | ✓ | `[13+]` — exclusive with `ShouldBeUnique` |
| **Bulk dispatch** (`Bus::bulk`) | — | — | ✓ | `[13+]` |
| **Queue routing** (per-job connection rules in config) | — | — | ✓ | `[13+]` |
| **Releasing jobs** middleware docs / patterns | — | — | ✓ | `[13+]` — `$job->release()` works in all versions |
| `Interruptible` interface (`interrupted()`) | — | — | ✓ | `[13+]` — react to SIGTERM in long jobs |
| SQS **fair queues** (standard queue message groups) | — | partial | ✓ | Expanded in 13 docs |
| SQS **overflow** storage (large payloads) | — | — | ✓ | `[13+]` — SQS only |
| `Preparing Jobs Before Dispatch` (`prepare()` on job) | — | — | ✓ | `[13+]` |

## Redis-specific (all versions)

| Topic | Versions | Notes |
|-------|----------|-------|
| `redis` queue driver | 11–13 | Primary driver for this skill |
| `block_for` blocking pop | 11–13 | `0` = indefinite block |
| Redis cluster hash tags in queue name | 11–13 | e.g. `'{default}'` |
| Rate limiting / overlaps via Redis cache | 11–13 | Middleware + `Redis::throttle` |
| Horizon (dashboard + workers) | 11–13 | Separate `laravel-horizon` skill |

## When version is unknown

1. Check `composer.json` / `composer show laravel/framework`
2. If still ambiguous, prefer **11-compatible** APIs (lowest common denominator)
3. Mention the minimum version if suggesting a newer API

## Adding Laravel 14+

Copy this table structure. Diff the new `queues.md` against 13.x. Add a `14` column or new `[14+]` rows. Update `SKILL.md` version table and affected reference files.
