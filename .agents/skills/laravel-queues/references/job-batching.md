# Job Batching

Batches group jobs with shared completion/failure callbacks. Requires `job_batches` table (default Laravel migration).

## Batchable job `[11+]`

```php
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportRow implements ShouldQueue
{
    use Batchable, Dispatchable, Queueable;

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }
        // ...
    }
}
```

## Dispatch batch `[11+]`

```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;

$batch = Bus::batch([
    new ImportRow(1),
    new ImportRow(2),
])
    ->name('Import CSV')
    ->onConnection('redis')
    ->onQueue('imports')
    ->allowFailures()
    ->then(function (Batch $batch) { /* all succeeded */ })
    ->catch(function (Batch $batch, Throwable $e) { /* first failure */ })
    ->finally(function (Batch $batch) { /* always */ })
    ->dispatch();

$batchId = $batch->id;
```

## Chains and batches `[11+]`

```php
Bus::chain([
    new PrepareImport,
    Bus::batch([new ImportRow(1), new ImportRow(2)]),
    new FinalizeImport,
])->dispatch();
```

## Add jobs to running batch `[11+]`

```php
$batch = Bus::findBatch($id);
$batch->add([new ImportRow(3)]);
```

## Inspect / cancel `[11+]`

```php
$batch = Bus::findBatch($id);
$batch->totalJobs;
$batch->processedJobs();
$batch->progress();       // 0-100
$batch->cancel();
$batch->finished();
$batch->cancelled();
$batch->failedJobs;
```

Return batch from route for frontend polling (documented pattern in Laravel docs).

## Pruning `[11+]`

```php
// config/queue.php batching section — prune_after in hours
php artisan queue:prune-batches --hours=48
```

Schedule in `routes/console.php`:

```php
Schedule::command('queue:prune-batches --hours=48')->daily();
```

## DynamoDB storage `[11+]` (optional)

Set `queue.batching.driver` to `dynamodb` with AWS credentials. Table keys: `application` (partition), `id` (sort). Use DynamoDB TTL for pruning instead of `queue:prune-batches`.

## Retry failed batch jobs `[11+]`

```php
$batch = Bus::findBatch($id);
$batch->retryFailed();
```

## Testing

See [testing.md](testing.md) — `Bus::fake()`, `assertBatched`, batch callbacks.
