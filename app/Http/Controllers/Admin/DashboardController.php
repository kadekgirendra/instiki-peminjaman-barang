<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Total Barang ──────────────────────────────────────
        $totalItems = Item::count();
        $totalItemsLastMonth = Item::where('created_at', '<=', now()->subMonth())->count();
        $totalItemsDelta = $this->percentChange($totalItemsLastMonth, $totalItems);

        // ── Pinjaman Aktif (status booked, snapshot saat ini) ──
        $pinjamanAktif = Transaction::where('status', 'booked')->count();
        // Dibanding volume transaksi yang mulai booked bulan lalu (approx, karena
        // tidak ada tabel histori status)
        $pinjamanAktifBulanLalu = Transaction::whereIn('status', ['booked', 'completed'])
            ->whereBetween('start_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();
        $pinjamanAktifDelta = $this->percentChange($pinjamanAktifBulanLalu, $pinjamanAktif);

        // ── Permintaan Tertunda ────────────────────────────────
        $permintaanTertunda = Transaction::where('status', 'pending')->count();
        $permintaanTertundaBulanLalu = Transaction::where('created_at', '<=', now()->subMonth())
            ->where('status', 'pending')
            ->count();
        $permintaanTertundaDelta = $this->percentChange($permintaanTertundaBulanLalu, $permintaanTertunda);

        // ── Aktivitas Terkini ──────────────────────────────────
        $recentActivities = Transaction::with(['item', 'user'])
            ->whereIn('status', ['booked', 'completed'])
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(function ($trx) {
                $isReturn = $trx->status === 'completed';

                return (object) [
                    'user_name' => $trx->user->name,
                    'user_nim'  => $trx->user->nim_nidn,
                    'item_name' => $trx->item->name,
                    'label'     => $isReturn ? 'Kembali' : 'Pinjam',
                    'is_return' => $isReturn,
                    'waktu'     => $this->diffForHumansId($trx->updated_at),
                ];
            });

        // ── Pengembalian Terlambat ─────────────────────────────
        $overdueReturns = Transaction::with(['item', 'user'])
            ->where('status', 'booked')
            ->where('end_date', '<', now()->toDateString())
            ->orderBy('end_date')
            ->take(5)
            ->get()
            ->map(function ($trx) {
                $daysLate = (int) Carbon::parse($trx->end_date)->diffInDays(now());

                return (object) [
                    'user_name' => $trx->user->name,
                    'user_nim'  => $trx->user->nim_nidn,
                    'item_name' => $trx->item->name,
                    'days_late' => $daysLate,
                    'severity'  => $daysLate <= 1 ? 'warning' : 'danger',
                ];
            });

        return view('admin.dashboard', compact(
            'totalItems',
            'totalItemsDelta',
            'pinjamanAktif',
            'pinjamanAktifDelta',
            'permintaanTertunda',
            'permintaanTertundaDelta',
            'recentActivities',
            'overdueReturns'
        ));
    }

    private function percentChange(int $before, int $after): ?float
    {
        if ($before === 0) {
            return null;
        }

        return round((($after - $before) / $before) * 100, 1);
    }

    private function diffForHumansId(Carbon $date): string
    {
        $seconds = now()->diffInSeconds($date);

        if ($seconds < 60) {
            return 'Baru saja';
        }

        $minutes = now()->diffInMinutes($date);
        if ($minutes < 60) {
            return $minutes . ' menit yang lalu';
        }

        $hours = now()->diffInHours($date);
        if ($hours < 24) {
            return $hours . ' jam yang lalu';
        }

        $days = now()->diffInDays($date);

        return $days . ' hari yang lalu';
    }
}
