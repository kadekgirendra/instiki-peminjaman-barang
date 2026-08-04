<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;

class CalendarController extends Controller
{
    public function index()
    {
        // Semua barang yang sedang keluar (booked) — FullCalendar yang urus
        // navigasi bulan di sisi client, jadi kita kirim semuanya sekali jalan.
        $events = Transaction::with(['item', 'user'])
            ->where('status', 'booked')
            ->get()
            ->map(fn($trx) => [
                'date'      => $trx->end_date->toDateString(),
                'item_name' => $trx->item->name,
                'quantity'  => $trx->quantity,
                'user_name' => $trx->user->name,
                'user_nim'  => $trx->user->nim_nidn,
            ])
            ->values();

        return view('admin.calendar.index', [
            'events'      => $events,
            'totalBooked' => $events->count(),
        ]);
    }
}
