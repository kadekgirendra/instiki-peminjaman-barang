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
        // Layout mahasiswa/dosen — pengingat pinjaman milik akun yang sedang login.
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

        // Layout admin — pengingat lintas SEMUA mahasiswa: barang yang sudah
        // telat atau jatuh tempo dalam 2 hari ke depan (bukan milik admin sendiri).
        View::composer('components.admin-layout', function ($view) {
            if (!Auth::check()) {
                $view->with('reminders', collect());
                return;
            }

            $reminders = Transaction::with(['item', 'user'])
                ->where('status', 'booked')
                ->where('end_date', '<=', now()->addDays(2)->toDateString())
                ->orderBy('end_date')
                ->take(5)
                ->get();

            $view->with('reminders', $reminders);
        });
    }
}
