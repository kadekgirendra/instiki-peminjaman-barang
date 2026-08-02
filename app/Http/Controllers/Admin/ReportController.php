<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->get('range', '7d');
        $category = $request->get('category', 'all');

        [$start, $end] = $this->resolveDateRange($range);

        $rows = $this->buildReportRows($start, $end, $category);

        $categories = Item::select('category')->distinct()
            ->pluck('category')->map(fn($c) => trim($c))->unique()->values();

        return view('admin.reports.index', [
            'rows'       => $rows,
            'categories' => $categories,
            'range'      => $range,
            'category'   => $category,
        ]);
    }

    public function export(Request $request)
    {
        $range = $request->get('range', '7d');
        $category = $request->get('category', 'all');

        [$start, $end] = $this->resolveDateRange($range);
        $rows = $this->buildReportRows($start, $end, $category);

        $filename = 'laporan-peminjaman-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Barang', 'Kategori', 'Total Dipinjam', 'Durasi Rata-rata (Hari)']);

            foreach ($rows as $row) {
                fputcsv($handle, [$row['name'], $row['category'], $row['total'], $row['avg_duration']]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function resolveDateRange(string $range): array
    {
        return match ($range) {
            '30d'        => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'all'        => [null, null],
            default      => [now()->subDays(7)->startOfDay(), now()->endOfDay()], // '7d'
        };
    }

    private function baseQuery(?Carbon $start, ?Carbon $end, string $category)
    {
        $query = Transaction::with('item')->whereIn('status', ['booked', 'completed']);

        if ($start && $end) {
            $query->whereBetween('start_date', [$start->toDateString(), $end->toDateString()]);
        }

        if ($category !== 'all') {
            $query->whereHas('item', fn($q) => $q->where('category', $category));
        }

        return $query;
    }

    private function buildReportRows(?Carbon $start, ?Carbon $end, string $category)
    {
        return $this->baseQuery($start, $end, $category)->get()
            ->groupBy('item_id')
            ->map(function ($group) {
                $item = $group->first()->item;
                $avgDays = $group->avg(fn($trx) => max(1, $trx->start_date->diffInDays($trx->end_date)));

                return [
                    'name'         => $item->name,
                    'category'     => $item->category,
                    'total'        => $group->sum('quantity'),
                    'avg_duration' => (int) round($avgDays),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }
}
