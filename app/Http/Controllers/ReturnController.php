<?php

namespace App\Http\Controllers;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    public function create(LoanRequest $loanRequest)
    {
        // Pastikan loan request ini milik user yang login
        abort_unless($loanRequest->user_id === Auth::id(), 403);

        $transactions = $loanRequest->transactions()->where('status', 'booked')->get();

        abort_if($transactions->isEmpty(), 404);

        return view('returns.create', compact('loanRequest', 'transactions'));
    }

    public function store(Request $request, LoanRequest $loanRequest)
    {
        abort_unless($loanRequest->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'note'  => 'nullable|string',
        ]);

        $photoPath = $request->file('photo')->store('return-proofs', 'public');

        $loanRequest->transactions()->where('status', 'booked')->update([
            'return_photo'        => $photoPath,
            'return_note'         => $validated['note'],
            'return_requested_at' => now(),
        ]);

        return redirect()->route('transactions.index')
            ->with('return_success', true);
    }
}
