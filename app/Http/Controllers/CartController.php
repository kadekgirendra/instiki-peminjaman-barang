<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if ($validated['quantity'] > $item->total_stock) {
            return back()->withErrors(['quantity' => 'Jumlah melebihi stok yang tersedia.']);
        }

        $cart = session('loan_cart', []);
        $cart[$validated['item_id']] = $validated['quantity'];
        session(['loan_cart' => $cart]);

        return redirect()->route('loan-requests.create');
    }

    public function remove(Item $item)
    {
        $cart = session('loan_cart', []);
        unset($cart[$item->id]);
        session(['loan_cart' => $cart]);

        return back();
    }
}
