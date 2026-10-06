# Job Middleware

Middleware wraps job execution. Attach via `middleware()` on the job (add method manually — not scaffolded by `make:job`):

```php
public function middleware(): array
{
    return [
        new RateLimited('albums'),
        (new WithoutOverlapping($this->user->id))->releaseAfter(60),
    ];
}
```

Also works on queueable listeners, mailables, and notifications.

## Rate limiting `[11+]`

Define limiter in `AppServiceProvider::boot()`:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('backups', function (object $job) {
    return $job->user->premium()
        ? Limit::none()
        : Limit::perHour(1)->by($job->user->id);
});
```

```php
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;  // Redis-optimized

return [new RateLimited('backups')];
// return [(new RateLimitedWithRedis('backups'))->dontRelease()];
```

## WithoutOverlapping `[11+]`

Prevents concurrent runs sharing a key. Requires cache locks.

```php
use Illuminate\Queue\Middleware\WithoutOverlapping;

return [(new WithoutOverlapping($this->order->id))->releaseAfter(60)];

// Share key across job classes
(new WithoutOverlapping('shared-key'))->shared();

// Don't release — drop overlapping job
(new WithoutOverlapping($this->order->id))->dontRelease();
```

## ThrottlesExceptions `[11+]`

Backoff after repeated exceptions without counting as full attempts:

```php
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\Middleware\ThrottlesExceptionsWithRedis;

return [
    (new ThrottlesExceptions(10, 5 * 60))->backoff(60),
    // WhenReachedMaxAttempts callback optional
];
```

## Skip / SkipIf `[11+]`

```php
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Queue\Middleware\SkipIf;

return [
    Skip::when($this->order->isCancelled()),
    Skip::unless($this->user->isActive()),
    SkipIf::when(fn () => $this->shouldSkip()),
];
```

## Releasing jobs `[11+]` API · `[13+]` dedicated section

Return job to queue with delay:

```php
// In handle() or middleware
return $this->release(30);  // seconds
```

Custom middleware example:

```php
public function handle(object $job, Closure $next): void
{
    if ($this->shouldDefer($job)) {
        $job->release(60);
        return;
    }
    $next($job);
}
```

## Custom middleware

```bash
php artisan make:job-middleware RateLimited   # [12+]
```

```php
namespace App\Jobs\Middleware;

use Closure;

class RateLimited
{
    public function handle(object $job, Closure $next): void
    {
        // ... obtain lock or throttle ...
        $next($job);
    }
}
```
