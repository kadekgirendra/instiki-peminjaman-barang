<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $activeLoans = Transaction::where('user_id', $userId)
            ->where('status', 'booked')
            ->count();

        $pendingRequests = Transaction::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $totalHistory = Transaction::where('user_id', $userId)->count();

        return view('dashboard', compact('activeLoans', 'pendingRequests', 'totalHistory'));
    }
}
