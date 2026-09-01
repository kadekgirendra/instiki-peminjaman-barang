<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->get('range', 'all');
        $category = $request->get('category', 'all');

        [$start, $end] = $this->resolveDateRange($range);

        $itemRows = $this->buildItemRows($start, $end, $category);
        $borrowerRows = $this->buildBorrowerRows($start, $end, $category);
        $summary = $this->buildSummary($start, $end, $category);
        $statusBreakdown = $this->buildStatusBreakdown($start, $end, $category);

        $categories = Item::categories();

        return view('admin.reports.index', [
            'itemRows' => $itemRows,
            'borrowerRows' => $borrowerRows,
            'summary' => $summary,
            'statusBreakdown' => $statusBreakdown,
            'categories' => $categories,
            'range' => $range,
            'category' => $category,
        ]);
    }

    public function export(Request $request)
    {
        $range = $request->get('range', 'all');
        $category = $request->get('category', 'all');

        [$start, $end] = $this->resolveDateRange($range);
        $itemRows = $this->buildItemRows($start, $end, $category);
        $borrowerRows = $this->buildBorrowerRows($start, $end, $category);

        $filename = 'laporan-peminjaman-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($itemRows, $borrowerRows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['LAPORAN PER BARANG']);
            fputcsv($handle, ['Barang', 'Kategori', 'Total Unit Dipinjam', 'Frekuensi Transaksi', 'Durasi Rata-rata (Hari)', 'Total Pendapatan (Denda)']);
            foreach ($itemRows as $row) {
                fputcsv($handle, [$row['name'], $row['category'], $row['total_unit'], $row['frequency'], $row['avg_duration'], $row['total_revenue']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['LAPORAN PER PEMINJAM']);
            fputcsv($handle, ['Nama', 'NIM/NIDN', 'Jumlah Pengajuan', 'Total Unit Dipinjam', 'Total Pendapatan']);
            foreach ($borrowerRows as $row) {
                fputcsv($handle, [$row['name'], $row['nim_nidn'], $row['total_requests'], $row['total_unit'], $row['total_fine']]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // Export PDF — ringkasan + breakdown status + kedua tabel laporan,
    // dirender dari view HTML terpisah (admin.reports.pdf) karena dompdf
    // tidak mendukung utility class Tailwind, cuma CSS biasa.
    public function exportPdf(Request $request)
    {
        $range = $request->get('range', 'all');
        $category = $request->get('category', 'all');

        [$start, $end] = $this->resolveDateRange($range);

        $itemRows = $this->buildItemRows($start, $end, $category);
        $borrowerRows = $this->buildBorrowerRows($start, $end, $category);
        $summary = $this->buildSummary($start, $end, $category);
        $statusBreakdown = $this->buildStatusBreakdown($start, $end, $category);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'itemRows' => $itemRows,
            'borrowerRows' => $borrowerRows,
            'summary' => $summary,
            'statusBreakdown' => $statusBreakdown,
            'rangeLabel' => $this->rangeLabel($range),
            'categoryLabel' => $category === 'all' ? 'Semua Kategori' : $category,
            'printedAt' => now()->translatedFormat('j F Y, H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = 'laporan-peminjaman-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function rangeLabel(string $range): string
    {
        return match ($range) {
            '30d' => '30 Hari Terakhir',
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'all' => 'Semua Waktu',
            default => '7 Hari Terakhir',
        };
    }

    private function resolveDateRange(string $range): array
    {
        return match ($range) {
            '30d' => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'all' => [null, null],
            default => [now()->subDays(7)->startOfDay(), now()->endOfDay()], // '7d'
        };
    }

    // Query dasar untuk laporan "yang benar-benar terjadi" (booked/completed).
    // Pending & rejected sengaja tidak masuk sini karena belum/tidak jadi
    // peminjaman nyata — tapi tetap dihitung terpisah di buildStatusBreakdown().
    private function baseQuery(?Carbon $start, ?Carbon $end, string $category)
    {
        $query = Transaction::with(['item', 'user'])->whereIn('status', ['booked', 'completed']);

        if ($start && $end) {
            $query->whereBetween('start_date', [$start->toDateString(), $end->toDateString()]);
        }

        if ($category !== 'all') {
            $query->whereHas('item', fn($q) => $q->where('category', $category));
        }

        return $query;
    }

    private function buildItemRows(?Carbon $start, ?Carbon $end, string $category)
    {
        return $this->reportCacheRemember(
        $this->reportCacheKey('item-rows', $start, $end, $category),
        function () use ($start, $end, $category) {
        return $this->baseQuery($start, $end, $category)->get()
            ->groupBy('item_id')
            ->map(function ($group) {
                $item = $group->first()->item;
                $avgDays = $group->avg(fn($trx) => max(1, $trx->start_date->diffInDays($trx->end_date)));

                return [
                    'name' => $item->name,
                    'category' => $item->category,
                    'total_unit' => $group->sum('quantity'),
                    'frequency' => $group->count(),
                    'avg_duration' => (int) round($avgDays),
                    // Cuma denda yang sudah ditandai lunas (paid_at terisi) yang
                    // dihitung sebagai "pendapatan" — konsisten dengan kartu
                    // Total Pendapatan di Dashboard.
                    'total_revenue' => (float) $group->filter(fn($trx) => $trx->paid_at !== null)->sum('total_fee'),
                ];
            })
            ->sortByDesc('total_unit')
            ->values();

        });

    }

    // Laporan per peminjam — dikelompokkan per loan_request supaya "jumlah
    // pengajuan" tidak dobel-hitung kalau 1 pengajuan berisi banyak barang.
    private function buildBorrowerRows(?Carbon $start, ?Carbon $end, string $category)
    {
        return $this->reportCacheRemember(
        $this->reportCacheKey('borrower-rows', $start, $end, $category),
        function () use ($start, $end, $category) {
        return $this->baseQuery($start, $end, $category)->get()
            ->groupBy('user_id')
            ->map(function ($group) {
                $user = $group->first()->user;

                return [
                    'name' => $user->name,
                    'nim_nidn' => $user->nim_nidn,
                    'total_requests' => $group->pluck('loan_request_id')->unique()->count(),
                    'total_unit' => $group->sum('quantity'),
                    // Sama seperti laporan per barang: cuma yang sudah lunas
                    // yang dihitung, supaya totalnya nyambung ke Total Pendapatan.
                    'total_fine' => (float) $group->filter(fn($trx) => $trx->paid_at !== null)->sum('total_fee'),
                ];
            })
            ->sortByDesc('total_unit')
            ->values();
        });
    }

    // Kartu ringkasan di atas halaman.
    private function buildSummary(?Carbon $start, ?Carbon $end, string $category)
    {
        return $this->reportCacheRemember(
        $this->reportCacheKey('summary', $start, $end, $category),
        function () use ($start, $end, $category) {
        $rows = $this->baseQuery($start, $end, $category)->get();

        $avgDuration = $rows->avg(fn($trx) => max(1, $trx->start_date->diffInDays($trx->end_date)));

        // Piutang: denda yang sudah tercatat (total_fee > 0) tapi belum
        // ditandai lunas (paid_at masih null) — supaya "Total Pendapatan"
        // (uang yang sudah masuk) dan "Belum Dibayar" (uang yang masih
        // harus ditagih) sama-sama kelihatan dan saling melengkapi.
        $totalUnpaid = (float) $rows->filter(fn($trx) => $trx->paid_at === null)->sum('total_fee');

        return [
            'total_transactions' => $rows->pluck('loan_request_id')->unique()->count(),
            'total_unit' => $rows->sum('quantity'),
            'total_revenue' => (float) $rows->filter(fn($trx) => $trx->paid_at !== null)->sum('total_fee'),
            'total_unpaid' => $totalUnpaid,
            'total_peminjam' => $rows->pluck('user_id')->unique()->count(),
            'avg_duration' => $rows->isEmpty() ? 0 : (int) round($avgDuration),
        ];

        });
    }

    // Breakdown semua status (termasuk pending & rejected) dalam rentang & kategori
    // yang sama, dihitung dari tanggal pengajuan dibuat (created_at) — supaya
    // permintaan yang ditolak/tertunda tetap kelihatan di rentang saat diajukan,
    // bukan hilang begitu saja karena tidak punya start_date yang relevan.
    private function buildStatusBreakdown(?Carbon $start, ?Carbon $end, string $category)
    {
        return $this->reportCacheRemember(
        $this->reportCacheKey('status-breakdown', $start, $end, $category),
        function () use ($start, $end, $category) {
        $query = Transaction::with('item');

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        if ($category !== 'all') {
            $query->whereHas('item', fn($q) => $q->where('category', $category));
        }

        $rows = $query->get()->groupBy('loan_request_id')->map(function ($group) {
            $statuses = $group->pluck('status')->unique();

            return $statuses->count() === 1
                ? $statuses->first()
                : ($statuses->contains('pending') ? 'pending' : 'booked');
        });

        return [
            'pending' => $rows->filter(fn($s) => $s === 'pending')->count(),
            'booked' => $rows->filter(fn($s) => $s === 'booked')->count(),
            'completed' => $rows->filter(fn($s) => $s === 'completed')->count(),
            'rejected' => $rows->filter(fn($s) => $s === 'rejected')->count(),
        ];

        });
    }
    /**
     * Cache hasil laporan selama 5 menit, per kombinasi filter (range+category).
     * TTL sengaja pendek (bukan 1 jam seperti Item::categories()) karena data
     * laporan berubah tiap kali admin approve/reject/complete/markPaid — cache
     * lama berisiko menampilkan angka yang sudah basi. 5 menit adalah kompromi:
     * cukup meredam beban kalau laporan dibuka berkali-kali berturut-turut
     * (misal admin refresh / lihat lalu export CSV lalu balik lagi), tapi tidak
     * membuat data "macet" terlalu lama kalau ada transaksi baru masuk.
     */
    private function reportCacheRemember(string $key, callable $callback)
    {
        return Cache::remember($key, 300, $callback);
    }

    private function reportCacheKey(string $method, ?Carbon $start, ?Carbon $end, string $category): string
    {
        $startKey = $start?->toDateString() ?? 'null';
        $endKey = $end?->toDateString() ?? 'null';

        return "report:{$method}:{$startKey}:{$endKey}:{$category}";
    }
}
