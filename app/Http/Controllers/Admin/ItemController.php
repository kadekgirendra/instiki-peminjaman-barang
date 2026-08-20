<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function __construct(protected AvailabilityService $availability) {}

    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', trim($request->category));
        }

        $items = $query->orderBy('name')->paginate(10)->withQueryString();

        // Stok "Tersedia" itu bukan kolom statis di tabel items — dihitung
        // real-time via AvailabilityService (total_stock dikurangi quantity
        // yang lagi status booked HARI INI), sama seperti logika yang dipakai
        // saat approve transaksi. total_stock sendiri memang tidak pernah
        // berkurang otomatis; itu representasi jumlah unit yang admin PUNYA,
        // bukan yang sedang tidak dipinjam.
        $today = now()->toDateString();

        $items->getCollection()->transform(function (Item $item) use ($today) {
            $item->available_stock = $this->availability->getAvailableStock($item, $today, $today);
            $item->borrowed_now = $item->total_stock - $item->available_stock;

            return $item;
        });

        $categories = Item::select('category')
            ->distinct()
            ->pluck('category')
            ->map(fn($c) => trim($c))
            ->unique()
            ->values();

        return view('admin.items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Item::select('category')->distinct()->pluck('category')->map(fn($c) => trim($c))->unique()->values();

        return view('admin.items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        Item::create($validated);

        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $categories = Item::select('category')->distinct()->pluck('category')->map(fn($c) => trim($c))->unique()->values();

        return view('admin.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($validated);

        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        // item_id di transactions pakai cascadeOnDelete — kalau item ini pernah
        // dipinjam, menghapusnya akan ikut menghapus riwayat transaksi & data revenue.
        // Cegah dulu supaya data historis tidak hilang tanpa sengaja.
        if ($item->transactions()->exists()) {
            return back()->withErrors([
                'error' => 'Barang "' . $item->name . '" tidak bisa dihapus karena sudah punya riwayat transaksi.',
            ]);
        }

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil dihapus.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'             => 'required|string|max:255',
            'category'         => 'required|string|max:100',
            'description'      => 'nullable|string',
            'total_stock'      => 'required|integer|min:0',
            'daily_fine_rate'  => 'required|integer|min:0',
            'image'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
    }
}
