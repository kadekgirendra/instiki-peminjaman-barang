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
        // Dulu ini dibandingkan ke jumlah yang MASIH pending & dibuat > sebulan lalu —
        // hampir selalu 0 karena permintaan pending biasa langsung diproses admin,
        // jadi delta-nya gak informatif. Diganti jadi tren VOLUME permintaan masuk
        // (semua status, bukan cuma yang masih pending) minggu ini vs minggu lalu —
        // ini yang sebenarnya mau dilihat admin: "permintaan masuk lagi naik/turun?"
        $permintaanMingguIni = Transaction::where('created_at', '>=', now()->subDays(7))->count();
        $permintaanMingguLalu = Transaction::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();
        $permintaanTertundaDelta = $this->percentChange($permintaanMingguLalu, $permintaanMingguIni);

        // ── Total Pendapatan (dari denda saja) ─────────────────
        // WAJIB pakai scope revenueEligible (booked/completed) — pending/rejected
        // tidak boleh ikut kehitung. Scope ini sudah ada dari fase sebelumnya tapi
        // belum pernah dipakai di mana pun.
        $totalRevenue = Transaction::revenueEligible()->sum('total_fee');
        $totalRevenueBulanLalu = Transaction::revenueEligible()
            ->whereBetween('returned_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('total_fee');
        $totalRevenueDelta = $this->percentChange((int) $totalRevenueBulanLalu, (int) $totalRevenue);

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
            'totalRevenue',
            'totalRevenueDelta',
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
