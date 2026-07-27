<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ItemController extends Controller
{
    public function __construct(protected AvailabilityService $availability) {}

    public function index(Request $request)
    {
        $items = Item::query();

        // Filter ketersediaan berdasarkan rentang tanggal (PRD poin 5A)
        if ($request->filled(['start_date', 'end_date'])) {
            $items = $items->get()->filter(function ($item) use ($request) {
                return $this->availability->isAvailable($item, $request->start_date, $request->end_date);
            });
        } else {
            $items = $items->get();
        }

        return view('items.index', compact('items'));
    }

    public function show(Item $item)
    {
        return view('items.show', compact('item'));
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
