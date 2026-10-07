# Dispatching Jobs

## Basic dispatch `[11+]`

```php
use App\Jobs\ProcessPodcast;

ProcessPodcast::dispatch($podcast);
dispatch(new ProcessPodcast($podcast));
ProcessPodcast::dispatchIf($condition, $podcast);
ProcessPodcast::dispatchUnless($condition, $podcast);
```

## Delayed `[11+]`

```php
ProcessPodcast::dispatch($podcast)->delay(now()->addMinutes(10));
```

## Synchronous `[11+]`

```php
ProcessPodcast::dispatchSync($podcast);
Bus::dispatchSync(new ProcessPodcast($podcast));
```

## Deferred / background `[12+]` only

Post-response without a queue worker:

```php
// Same PHP process, after HTTP response
ProcessPodcast::dispatch($podcast)->onConnection('deferred');

// Separate spawned PHP process
ProcessPodcast::dispatch($podcast)->onConnection('background');
```

`deferred` is the default failover target in Laravel's example failover config.

## Bulk dispatch `[13+]` only

```php
use Illuminate\Support\Facades\Bus;

Bus::bulk([
    new ProcessPodcast($podcast1),
    new ProcessPodcast($podcast2),
]);
```

## Database transactions `[11+]`

```php
// config/queue.php — connection level
'after_commit' => true,

// Per dispatch
ProcessPodcast::dispatch($podcast)->afterCommit();

// Override connection default
ProcessPodcast::dispatch($podcast)->beforeCommit();
```

## Job chaining `[11+]`

```php
use Illuminate\Support\Facades\Bus;

Bus::chain([
    new ProcessPodcast($podcast),
    new ReleasePodcast($podcast),
    function () { /* closure */ },
])->dispatch();

// On failure — stop chain (default) or catch
Bus::chain([...])->catch(function (Throwable $e) { ... })->dispatch();

// Connection / queue for entire chain
Bus::chain([...])->onConnection('redis')->onQueue('podcasts')->dispatch();
```

Append to chain: `$chain->append(new AnotherJob);` (variable holding pending chain).

## Queue and connection `[11+]`

```php
ProcessPodcast::dispatch($podcast)
    ->onConnection('redis')
    ->onQueue('emails');

// Named queue on default connection
ProcessPodcast::dispatch($podcast)->onQueue('high');
```

### Queue routing `[13+]` only

In `config/queue.php`, route job classes to connections/queues:

```php
'routing' => [
    App\Jobs\ProcessPodcast::class => [
        'connection' => 'redis',
        'queue' => 'podcasts',
    ],
],
```

## Retries, timeouts, exceptions `[11+]` / `[12+]`

On job class:

```php
public int $tries = 5;
public int $maxExceptions = 3;       // [12+]
public int $timeout = 120;
public int $backoff = 60;             // [12+]
public bool $failOnTimeout = true;    // [12+]

public function backoff(): array     // [12+]
{
    return [1, 5, 10];
}
```

At dispatch:

```php
ProcessPodcast::dispatch($podcast)
    ->tries(5)
    ->maxExceptions(3)    // [12+]
    ->timeout(120)
    ->failOnTimeout(true);
```

### Fail on specific exceptions `[12+]`

```php
use Illuminate\Queue\Attributes\FailOn;

#[FailOn(InvalidArgumentException::class)]
class ProcessPodcast implements ShouldQueue { ... }

// Or method
public function failed(Throwable $e): void { ... }
// Use $this->fail($e) in handle to fail without retry
```

## Queue failover `[12+]` only

```php
// config/queue.php
'failover' => [
    'driver' => 'failover',
    'connections' => ['redis', 'database', 'sync'],
],
```

```env
QUEUE_CONNECTION=failover
```

Run a worker per connection in the list. With Horizon, only Redis is managed — run `queue:work database` separately for database failover.

Event: `Illuminate\Queue\Events\QueueFailedOver`.

## SQS FIFO `[12+]` (not Redis)

For completeness when projects use SQS:

```php
ProcessPodcast::dispatch($podcast)->onGroup('tenant-1');

public function deduplicationId(): string
{
    return (string) $this->podcast->id;
}
```

Fair queues on standard SQS: assign message groups per tenant/workload `[13+]` expanded docs.

## Error handling in handle `[11+]`

```php
public function handle(): void
{
    if ($this->shouldRetryLater()) {
        $this->release(30);
        return;
    }
    if ($this->isUnrecoverable()) {
        $this->fail(new Exception('Unrecoverable'));
        return;
    }
}
```
