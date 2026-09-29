<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReturnController extends Controller
{
    public function create(LoanRequest $loanRequest)
    {
        // Pastikan loan request ini milik user yang login
        $this->authorize('returnItem', $loanRequest);

        $transactions = $loanRequest->transactions()->where('status', 'booked')->get();

        abort_if($transactions->isEmpty(), 404);

        return view('returns.create', compact('loanRequest', 'transactions'));
    }

    public function store(Request $request, LoanRequest $loanRequest)
    {
        $this->authorize('returnItem', $loanRequest);

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'note' => 'nullable|string',
        ]);

        $photoPath = $request->file('photo')->store('return-proofs', 'public');

        $loanRequest->transactions()->where('status', 'booked')->update([
            'return_photo' => $photoPath,
            'return_note' => $validated['note'],
            'return_requested_at' => now(),
        ]);

        return redirect()->route('transactions.index')
            ->with('return_success', true);
    }

    public function showProof(LoanRequest $loanRequest)
    {
        // Pakai ability 'view' (bukan 'returnItem') — ini konteksnya melihat
        // hasil, bukan mengajukan pengembalian. Otorisasinya sama persis:
        // admin ATAU pemilik pengajuan.
        $this->authorize('view', $loanRequest);

        $returnPhoto = $loanRequest->transactions()->whereNotNull('return_photo')->value('return_photo');

        abort_if(empty($returnPhoto), 404);

        $path = Storage::disk('local')->path($returnPhoto);

        if (file_exists($path)) {
            return response()->file($path);
        }

        // Fallback untuk berkas lama yang diunggah ke disk 'public' sebelum
        // migrasi ke private storage.
        $publicPath = Storage::disk('public')->path($returnPhoto);

        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }

        abort(404);
    }
}
