<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Barang yang sedang dipinjam (status booked)
        $activeLoanGroups = Transaction::with('item', 'loanRequest')
            ->where('user_id', $userId)
            ->where('status', 'booked')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('loan_request_id');

        $requests = Transaction::with('item')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'booked', 'rejected'])
            ->orderByDesc('created_at')
            ->get();

        return view('transactions.index', compact('activeLoanGroups', 'requests'));
    }

    public function history()
    {
        $userId = Auth::id();

        $history = Transaction::with('item')
            ->where('user_id', $userId)
            ->whereIn('status', ['completed', 'rejected'])
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('loan_request_id');

        return view('transactions.history', compact('history'));
    }
}
