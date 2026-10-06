# Running Queue Workers

Primary driver for this skill: **Redis**. Use Horizon for production Redis pools (see `laravel-horizon`).

## `queue:work` `[11+]`

```shell
php artisan queue:work redis
php artisan queue:work redis --queue=high,default
php artisan queue:work redis --tries=3 --timeout=90 --sleep=3
php artisan queue:work redis --max-jobs=1000 --max-time=3600
php artisan queue:work --once
php artisan queue:work --stop-when-empty
php artisan queue:work --force          # during maintenance mode
php artisan queue:work -v
```

| Option | Purpose |
|--------|---------|
| `--queue=high,default` | Priority order (left first) |
| `--timeout` | Kill job after N seconds (default 60) |
| `--sleep` | Seconds to wait when queue empty |
| `--max-jobs` | Exit after N jobs (memory hygiene with Supervisor) |
| `--max-time` | Exit after N seconds |
| `--stop-when-empty` | Process all then exit (containers) |

`queue:listen` reloads code each job — slower; rarely needed in production.

## Redis `block_for` `[11+]`

```php
'block_for' => 5,  // block up to 5s waiting for jobs
```

`block_for => 0` blocks indefinitely and delays signal handling.

## Deployment `[11+]`

Workers cache booted application — restart after deploy:

```shell
php artisan queue:restart
```

Requires configured cache driver. Supervisor/systemd restarts the process.

**With Horizon:** use `php artisan horizon:terminate` instead.

## Worker signals & Interruptible `[13+]`

Jobs implementing `Illuminate\Contracts\Queue\Interruptible` receive `interrupted(int $signal)` on SIGTERM/SIGINT during execution:

```php
class ImportProducts implements ShouldQueue, Interruptible
{
    protected bool $shouldStop = false;

    public function handle(): void
    {
        foreach ($this->import->pendingRows() as $row) {
            if ($this->shouldStop) break;
            // ...
        }
    }

    public function interrupted(int $signal): void
    {
        $this->shouldStop = true;
    }
}
```

## Pause and resume `[12+]` only

```shell
php artisan queue:pause redis:default
php artisan queue:continue redis:default
```

Current job finishes; no new jobs until resumed. Worker process keeps running.

### Disable interruption polling `[12+]`

Performance optimization when restart/pause not needed:

```php
// AppServiceProvider::boot()
Queue::withoutInterruptionPolling();

// Or individually
Worker::$restartable = false;
Worker::$pausable = false;
```

## Supervisor `[11+]`

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=forge
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

**Critical:** `stopwaitsecs` > longest job runtime.

```shell
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start "laravel-worker:*"
```

## `retry_after` vs `--timeout` `[11+]`

- `retry_after` in `config/queue.php` — when job is released back if not deleted
- `--timeout` — when worker kills the process
- **Rule:** `--timeout` < `retry_after` (by several seconds)

## Monitoring `[11+]`

- Horizon dashboard (Redis + Horizon skill)
- `php artisan queue:monitor redis:default,redis:high --max=100` — fires `QueueBusy` event
- Third-party: Laravel Nightwatch, etc.

## Clear queues `[11+]`

```shell
php artisan queue:clear redis --queue=emails
```
