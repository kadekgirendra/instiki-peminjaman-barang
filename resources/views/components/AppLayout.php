<?php

namespace App\View\Components;

use App\Models\Transaction;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class AppLayout extends Component
{
    public function render(): View
    {
        $reminders = collect();

        if (auth()->check()) {
            $reminders = Transaction::with('item')
                ->where('user_id', auth()->id())
                ->where('status', 'booked')
                ->where('end_date', '<=', now()->addDays(2))
                ->orderBy('end_date')
                ->get();
        }

        return view('components.app-layout', compact('reminders'));
    }
}