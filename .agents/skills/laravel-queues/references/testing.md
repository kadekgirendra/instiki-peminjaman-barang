# Testing Queues

Use `Illuminate\Support\Facades\Bus` and `Illuminate\Support\Facades\Queue` fakes in PHPUnit/Pest.

## Fake all jobs `[11+]`

```php
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessPodcast;

Bus::fake();

// ... test code that dispatches jobs ...

Bus::assertDispatched(ProcessPodcast::class);
Bus::assertDispatched(ProcessPodcast::class, function ($job) use ($podcast) {
    return $job->podcast->id === $podcast->id;
});
Bus::assertNotDispatched(AnotherJob::class);
Bus::assertDispatchedTimes(ProcessPodcast::class, 2);
Bus::assertNothingDispatched();
```

## Fake subset `[11+]`

```php
Bus::fake([ProcessPodcast::class]);

// Other jobs run synchronously for real
```

## Chains `[11+]`

```php
Bus::fake();

Bus::assertChained([
    ProcessPodcast::class,
    ReleasePodcast::class,
]);

Bus::assertDispatched(ReleasePodcast::class);
```

### Chain modifications `[11+]`

```php
Bus::assertHasChain([...]);
Bus::assertDoesntHaveChain([...]);
```

### Chained batches `[11+]`

```php
Bus::assertChained([
    PrepareImport::class,
    Bus::batch([ImportRow::class, ImportRow::class]),
    FinalizeImport::class,
]);
```

## Batches `[11+]`

```php
Bus::fake();

Bus::assertBatched(function ($batch) {
    return $batch->jobs->count() === 3
        && $batch->name === 'Import CSV';
});

Bus::assertBatchCount(1);
Bus::assertNothingBatched();
```

### Job/batch interaction `[11+]`

Inside a test job, assert batch state with `$this->batch()` when using `Bus::fake()` batch callbacks.

## Queue interactions `[11+]`

```php
use Illuminate\Support\Facades\Queue;

Queue::fake();

Queue::assertPushed(ProcessPodcast::class);
Queue::assertPushedOn('high', ProcessPodcast::class);
Queue::assertPushed(ProcessPodcast::class, fn ($job) => true);
Queue::assertNotPushed(ProcessPodcast::class);
```

## Sync dispatch in tests

```php
Bus::dispatchSync(new ProcessPodcast($podcast));  // runs immediately
```

Or don't fake — use `sync` driver in `phpunit.xml` / `.env.testing`:

```env
QUEUE_CONNECTION=sync
```

## Events `[11+]`

```php
Event::fake([JobFailed::class, JobProcessed::class]);
```

Debounced `[13+]`: `JobDebounced::class`.
