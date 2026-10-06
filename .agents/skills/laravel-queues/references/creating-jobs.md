# Creating Jobs

## Generate

```bash
php artisan make:job ProcessPodcast
php artisan make:job-middleware RateLimited   # [12+]
```

Jobs live in `app/Jobs/`, implement `Illuminate\Contracts\Queue\ShouldQueue`, use `Illuminate\Foundation\Queue\Queueable`.

## Class structure

```php
namespace App\Jobs;

use App\Models\Podcast;
use App\Services\AudioProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPodcast implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public Podcast $podcast) {}

    public function handle(AudioProcessor $processor): void
    {
        // Container injects handle() dependencies
    }
}
```

### Eloquent in constructors

Models serialize as IDs only; relationships are re-loaded on `handle()` unless constrained.

```php
// Shrink payload — avoid serializing relations
$this->podcast = $podcast->withoutRelations();
$this->podcast = $podcast->withoutRelation('comments');

// [12+] attribute on property or class
use Illuminate\Queue\Attributes\WithoutRelations;

#[WithoutRelations]
class ProcessPodcast implements ShouldQueue { ... }

public function __construct(
    #[WithoutRelations] public Podcast $podcast,
) {}
```

Collections/arrays of models do **not** restore relationships when deserialized.

### `handle` DI customization `[12+]`

```php
// AppServiceProvider::boot()
use App\Jobs\ProcessPodcast;
use App\Services\AudioProcessor;

$this->app->bindMethod([ProcessPodcast::class, 'handle'], function (ProcessPodcast $job, $app) {
    return $job->handle($app->make(AudioProcessor::class));
});
```

## Unique jobs `[11+]`

Requires cache driver with atomic locks (redis, memcached, database, etc.). **Does not apply inside batches.**

```php
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique
{
    public function __construct(public Product $product) {}

    public function uniqueId(): string
    {
        return (string) $this->product->id;
    }
}
```

### Extended unique APIs `[12+]`

```php
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Queue\Attributes\UniqueFor;

#[UniqueFor(3600)]
class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique { ... }

// Unlock when processing starts (not when finished)
class Job implements ShouldQueue, ShouldBeUniqueUntilProcessing { ... }

public function uniqueVia(): Repository
{
    return Cache::driver('redis');
}
```

## Debounced jobs `[13+]` only

Only latest dispatch within window runs. **Cannot** use with `ShouldBeUnique`.

```php
use Illuminate\Queue\Attributes\DebounceFor;

#[DebounceFor(30)]                    // optional maxWait: 120
class UpdateSearchIndex implements ShouldQueue
{
    public function __construct(public int $productId) {}

    public function debounceId(): string
    {
        return (string) $this->productId;
    }

    public function debounceVia(): Repository
    {
        return Cache::driver('redis');
    }
}
```

Superseded dispatches fire `Illuminate\Queue\Events\JobDebounced`.

## Encrypted jobs `[11+]`

```php
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class SensitiveJob implements ShouldQueue, ShouldBeEncrypted { ... }
```

## Prepare before dispatch `[13+]`

Define `prepare()` on the job to run logic synchronously before the job is pushed (e.g. heavy setup that should not run on the worker):

```php
public function prepare(): void
{
    $this->data = $this->expensiveComputation();
}
```

## Queueing closures `[11+]`

```php
use Illuminate\Support\Facades\Queue;

Queue::push(function () {
    // ...
});
```

Prefer dedicated job classes for maintainability and testability.
