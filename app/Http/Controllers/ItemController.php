<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

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

        $items = $query->paginate(12)->withQueryString();

        if ($request->filled(['start_date', 'end_date'])) {
            // 1 query buat semua barang di halaman ini, bukan 1 query per barang.
            $stockMap = $this->availability->getAvailableStockBulk(
                $items->getCollection(),
                $request->start_date,
                $request->end_date
            );

            $items->through(function (Item $item) use ($stockMap) {
                $item->available_stock = $stockMap[$item->id];

                return $item;
            });
        } else {
            $items->through(function (Item $item) {
                $item->available_stock = $item->total_stock;

                return $item;
            });
        }

        $categories = Item::categories();

        return view('items.index', compact('items', 'categories'));
    }

    public function show(Item $item)
    {
        $availableStock = $item->total_stock;

        return view('items.show', compact('item', 'availableStock'));
    }

    /**
     * Endpoint JSON untuk kalender interaktif — dikonsumsi oleh JS date picker.
     */
    public function calendarData(Request $request, Item $item)
    {
        $rangeStart = $request->input('start', now()->startOfMonth()->toDateString());
        $rangeEnd = $request->input('end', now()->addMonths(2)->endOfMonth()->toDateString());

        $fullyBookedDates = $this->availability->getFullyBookedDates($item, $rangeStart, $rangeEnd);

        return response()->json([
            'fully_booked_dates' => $fullyBookedDates,
        ]);
    }
}
