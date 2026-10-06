# Failed Jobs

## Storage `[11+]`

Default: `failed_jobs` database table.

```shell
php artisan make:queue-failed-table
php artisan migrate
```

```php
// config/queue.php
'failed' => [
    'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
    'database' => env('DB_CONNECTION', 'sqlite'),
    'table' => 'failed_jobs',
],
```

DynamoDB driver `[11+]`: set `driver` => `dynamodb` with AWS keys; table keys `application` + `uuid`.

Disable storage: `'driver' => 'null'`.

## `failed()` method `[11+]`

```php
use Throwable;

public function failed(?Throwable $exception): void
{
    // Notify, cleanup, log...
}
```

## Cleaning up `[11+]`

`deleteWhenMissingModels` property — skip retries when Eloquent model missing:

```php
public bool $deleteWhenMissingModels = true;
```

Or `ShouldBeUnique`-style: `Illuminate\Queue\Attributes\DeleteWhenMissingModels` `[12+]`.

## Retry / manage `[11+]`

```shell
php artisan queue:failed                    # list
php artisan queue:retry 5                     # by ID
php artisan queue:retry all
php artisan queue:forget 5
php artisan queue:flush                       # delete all
php artisan queue:prune-failed --hours=48
```

Schedule pruning:

```php
Schedule::command('queue:prune-failed --hours=48')->daily();
```

## Failed job events `[11+]`

Listen in `AppServiceProvider` or dedicated listener:

```php
use Illuminate\Queue\Events\JobFailed;

Event::listen(JobFailed::class, function (JobFailed $event) {
    // $event->job, $event->exception
});
```

## Max attempts `[11+]`

Exceeded attempts → inserted into `failed_jobs` (async jobs only). Sync jobs throw immediately.

Configure via `$tries`, `->tries()`, worker `--tries`, or time-based `$backoff` / `$maxExceptions` `[12+]`.

## Horizon overlap

```shell
php artisan horizon:forget {id}
php artisan horizon:forget --all
```

See Horizon skill for dashboard failed-job management.
