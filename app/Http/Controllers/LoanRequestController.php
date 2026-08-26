<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanRequestController extends Controller
{
    public function __construct(protected AvailabilityService $availability)
    {
    }

    public function create()
    {
        $cart = session('loan_cart', []);

        if (empty($cart)) {
            return redirect()->route('items.index')
                ->with('status', 'Pilih barang terlebih dahulu untuk mulai meminjam.');
        }

        $cartItems = Item::whereIn('id', array_keys($cart))->get()->map(function ($item) use ($cart) {
            $item->cart_quantity = $cart[$item->id];
            return $item;
        });

        $catalogItems = Item::all()->map(function ($item) use ($cart) {
            $item->available_stock = $item->total_stock;
            $item->cart_quantity = $cart[$item->id] ?? 0;
            return $item;
        });

        $categories = Item::categories();

        $prefillDates = session('loan_prefill_dates', ['start_date' => null, 'end_date' => null]);

        return view('loan-requests.create', compact('cartItems', 'catalogItems', 'categories', 'prefillDates'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'purpose' => 'nullable|string|max:500',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $cart = session('loan_cart', []);

        if (empty($cart)) {
            return back()->withErrors(['cart' => 'Keranjang peminjaman kosong.']);
        }

        $documentPath = $request->file('document')->store('documents', 'public');

        try {
            // $items di-return dari dalam transaction, supaya bisa dipakai lagi
            // di luar untuk pesan sukses (loan_items_summary) tanpa query ulang.
            $items = DB::transaction(function () use ($validated, $cart, $documentPath) {
                // Kunci baris Item terkait (urut ID) SEBELUM cek ketersediaan —
                // mencegah dua submit yang bersaing untuk barang yang sama lolos
                // cek stok secara bersamaan.
                $lockedItems = $this->availability->lockItems(array_keys($cart));

                foreach ($lockedItems as $item) {
                    $qty = $cart[$item->id];

                    if (
                        !$this->availability->isAvailable(
                            $item,
                            $validated['start_date'],
                            $validated['end_date'],
                            $qty,
                            lock: true
                        )
                    ) {
                        throw new \RuntimeException(
                            "Stok \"{$item->name}\" tidak mencukupi untuk jumlah/tanggal yang dipilih."
                        );
                    }
                }

                $loanRequest = LoanRequest::create([
                    'user_id' => Auth::id(),
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'purpose' => $validated['purpose'],
                    'document_path' => $documentPath,
                ]);

                foreach ($lockedItems as $item) {
                    Transaction::create([
                        'loan_request_id' => $loanRequest->id,
                        'user_id' => Auth::id(),
                        'item_id' => $item->id,
                        'start_date' => $validated['start_date'],
                        'end_date' => $validated['end_date'],
                        'purpose' => $validated['purpose'] ?? '-',
                        'quantity' => $cart[$item->id],
                        'status' => 'pending',
                    ]);
                }

                return $lockedItems;
            });
        } catch (\RuntimeException $e) {
            // Booking gagal (stok tidak cukup) — hapus dokumen yang sudah
            // terlanjur ter-upload supaya tidak jadi file sampah di storage.
            \Storage::disk('public')->delete($documentPath);

            return back()->withErrors(['cart' => $e->getMessage()]);
        }

        session()->forget('loan_cart');

        return redirect()->route('transactions.index')->with([
            'loan_success' => true,
            'loan_items_summary' => $items->pluck('name')->implode(', '),
            'loan_start_date' => \Carbon\Carbon::parse($validated['start_date'])->translatedFormat('j F Y'),
            'loan_end_date' => \Carbon\Carbon::parse($validated['end_date'])->translatedFormat('j F Y'),
        ]);
    }
}
