<?php

namespace App\Providers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.app-layout', function ($view) {

        if (!Auth::check()) {
            $view->with('reminders', collect());
            return;
        }

        $reminders = Transaction::with('item')
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'booked'])
            ->latest()
            ->take(5)
            ->get();

        $view->with('reminders', $reminders);
    });
    }
}
