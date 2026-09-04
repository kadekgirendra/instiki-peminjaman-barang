<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;

class ItemController extends Controller
{
    public function __construct(protected AvailabilityService $availability) {}

    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
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

        $stockMap = $this->availability->getAvailableStockBulk($items->getCollection(), $today, $today);

        $items->getCollection()->transform(function (Item $item) use ($stockMap) {
            $item->available_stock = $stockMap[$item->id];
            $item->borrowed_now = $item->total_stock - $item->available_stock;

            return $item;
        });

        $categories = Item::categories();

        return view('admin.items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Item::categories();

        return view('admin.items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeResizedImage($request->file('image'));
        }

        Item::create($validated);
        Item::forgetCategoriesCache();

        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $categories = Item::categories();

        return view('admin.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $$validated['image'] = $this->storeResizedImage($request->file('image'));
        }

        $item->update($validated);
        Item::forgetCategoriesCache();

        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        // Dulu ada guard "tolak hapus kalau punya riwayat transaksi" — sekarang
        // TIDAK PERLU LAGI. Item pakai SoftDeletes, jadi delete() di sini cuma
        // mengisi kolom deleted_at (UPDATE), bukan DELETE sungguhan — foreign key
        // cascadeOnDelete di transactions.item_id tidak pernah ter-trigger,
        // sehingga riwayat transaksi & data revenue tetap aman meski barang ini
        // "dihapus" dari tampilan katalog.
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();
        Item::forgetCategoriesCache();

        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil dihapus.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'total_stock' => 'required|integer|min:0',
            'daily_fine_rate' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
    }

    /**
     * Resize gambar sebelum disimpan — foto barang dari kamera HP bisa
     * beresolusi 3000x4000px+, padahal cuma ditampilkan di kotak kecil
     * (h-44 di katalog). Tanpa resize, browser tetap download file penuh
     * meski ditampilkan kecil — buang-buang bandwidth tanpa nambah kualitas
     * tampilan. Semua format input (jpg/png) diseragamkan jadi .jpg di sini
     * supaya ukuran file makin kecil (PNG biasanya jauh lebih besar dari JPG
     * untuk foto biasa, dan barang di sini tidak butuh transparansi).
     */
    private function storeResizedImage(UploadedFile $file): string
    {
        $image = Image::decode($file)->scaleDown(width: 800);

        $filename = 'items/'.Str::random(40).'.jpg';

        $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 80);

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }
}
