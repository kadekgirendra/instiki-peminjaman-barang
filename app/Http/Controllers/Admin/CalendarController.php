<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $search = $request->get('search');

        $query = Transaction::with(['item', 'user'])
            ->where('status', 'booked');

        if ($category !== 'all') {
            $query->whereHas('item', fn($q) => $q->where('category', $category));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', fn($i) => $i->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('nim_nidn', 'like', "%{$search}%"));
            });
        }

        $bookedTransactions = $query->get();

        // Tiap transaksi booked menghasilkan DUA event di kalender: tanggal mulai
        // pinjam (biar admin lihat kapan barang keluar) dan tanggal jatuh tempo
        // (biar admin lihat kapan harus balik). Dulu cuma jatuh tempo yang dikirim.
        $events = collect();

        foreach ($bookedTransactions as $trx) {
            $isOverdue = $trx->is_overdue;

            $baseInfo = [
                'transaction_id' => $trx->id,
                'item_name'      => $trx->item->name,
                'category'       => $trx->item->category,
                'quantity'       => $trx->quantity,
                'user_id'        => $trx->user_id,
                'user_name'      => $trx->user->name,
                'user_nim'       => $trx->user->nim_nidn,
                'purpose'        => $trx->purpose,
            ];

            $events->push(array_merge($baseInfo, [
                'date' => $trx->start_date->toDateString(),
                'type' => 'start',
                'label' => 'Mulai Pinjam',
            ]));

            $events->push(array_merge($baseInfo, [
                'date' => $trx->end_date->toDateString(),
                'type' => $isOverdue ? 'overdue' : 'due',
                'label' => $isOverdue ? 'Terlambat' : 'Jatuh Tempo',
            ]));
        }

        $events = $events->values();

        // Kartu ringkasan di atas kalender — angka-angka yang paling sering
        // dicari admin sekali lihat, tanpa perlu klik-klik kalender dulu.
        $today = now()->toDateString();
        $summary = [
            'total_booked'  => $bookedTransactions->count(),
            'due_today'     => $bookedTransactions->filter(fn($t) => $t->end_date->toDateString() === $today)->count(),
            'overdue'       => $bookedTransactions->filter(fn($t) => $t->is_overdue)->count(),
            'starting_today' => $bookedTransactions->filter(fn($t) => $t->start_date->toDateString() === $today)->count(),
        ];

        $categories = Item::select('category')->distinct()
            ->pluck('category')->map(fn($c) => trim($c))->unique()->values();

        return view('admin.calendar.index', [
            'events'     => $events,
            'summary'    => $summary,
            'categories' => $categories,
            'category'   => $category,
            'search'     => $search,
        ]);
    }
}
