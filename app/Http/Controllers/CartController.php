<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        \Log::info('CartController@add dipanggil', $request->all());
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if ($validated['quantity'] > $item->total_stock) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Jumlah melebihi stok yang tersedia.'], 422);
            }
            return back()->withErrors(['quantity' => 'Jumlah melebihi stok yang tersedia.']);
        }

        $cart = session('loan_cart', []);
        $cart[$validated['item_id']] = $validated['quantity'];
        session(['loan_cart' => $cart]);

        // Simpan tanggal kalau dikirim (dari Katalog dengan filter tanggal aktif)
        if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
            session([
                'loan_prefill_dates' => [
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                ]
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'quantity' => $validated['quantity'],
                ],
            ]);
        }

        return redirect()->route('loan-requests.create');
    }
    public function remove(Request $request, Item $item)
    {
        $cart = session('loan_cart', []);
        unset($cart[$item->id]);
        session(['loan_cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
