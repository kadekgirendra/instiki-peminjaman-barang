<?php

namespace App\Http\Controllers;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Barang yang sedang dipinjam (status booked)
        $activeLoans = Transaction::with('item')
            ->where('user_id', $userId)
            ->where('status', 'booked')
            ->orderByDesc('created_at')
            ->get();

        // Permintaan yang masih pending atau baru disetujui (belum completed)
        $requests = Transaction::with('item')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'booked'])
            ->orderByDesc('created_at')
            ->get();

        return view('transactions.index', compact('activeLoans', 'requests'));
    }

    public function history()
    {
        $userId = Auth::id();

        $history = Transaction::with('item')
            ->where('user_id', $userId)
            ->whereIn('status', ['completed', 'rejected'])
            ->orderByDesc('updated_at')
            ->get();

        return view('transactions.history', compact('history'));
    }
}
