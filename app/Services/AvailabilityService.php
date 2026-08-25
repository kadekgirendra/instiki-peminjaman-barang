<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
    /**
     * Hitung total quantity yang sudah ter-booking (status = booked)
     * untuk item tertentu, yang rentang tanggalnya bersinggungan (overlap)
     * dengan rentang tanggal yang diminta.
     *
     * $lock = true akan menambahkan FOR UPDATE ke query ini — WAJIB dipanggil
     * di dalam DB::transaction(), supaya baris transaksi 'booked' yang overlap
     * ikut terkunci sampai transaction ini selesai. Ini mencegah request lain
     * yang berjalan bersamaan membaca angka stok yang sama sebelum baris ini
     * benar-benar ter-commit.
     */
    public function getBookedQuantity(Item $item, string $startDate, string $endDate, bool $lock = false): int
    {
        $query = Transaction::where('item_id', $item->id)
            ->where('status', 'booked')
            ->overlapping($startDate, $endDate);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->sum('quantity');
    }

    /**
     * Stok aktual = Total Stok - Jumlah yang sudah booked di rentang tanggal itu.
     */
    public function getAvailableStock(Item $item, string $startDate, string $endDate, bool $lock = false): int
    {
        $booked = $this->getBookedQuantity($item, $startDate, $endDate, $lock);

        return max($item->total_stock - $booked, 0);
    }

    /**
     * Cek apakah item tersedia untuk quantity tertentu di rentang tanggal tersebut.
     */
    public function isAvailable(Item $item, string $startDate, string $endDate, int $quantity = 1, bool $lock = false): bool
    {
        return $this->getAvailableStock($item, $startDate, $endDate, $lock) >= $quantity;
    }

    /**
     * Kunci baris Item terkait (SELECT ... FOR UPDATE) sebelum proses cek+booking.
     * Dipanggil di awal, di dalam DB::transaction(), SEBELUM isAvailable() dipanggil.
     * Ini yang benar-benar "menyerialkan" request-request yang bersaing untuk
     * barang yang sama — karena request kedua akan MENUNGGU (blocked) sampai
     * request pertama commit/rollback, baru boleh baca & proses.
     *
     * Kalau ada beberapa item sekaligus (1 pengajuan berisi banyak barang),
     * SELALU urutkan berdasarkan ID (orderBy('id')) sebelum dikunci — supaya
     * request manapun yang datang, urutan penguncian antar baris selalu SAMA.
     * Kalau tidak diurutkan, dua request yang mengunci item yang sama tapi
     * dengan urutan berbeda bisa saling menunggu satu sama lain selamanya
     * (deadlock), dan salah satunya akan digagalkan otomatis oleh MySQL.
     */
    public function lockItems(array $itemIds): \Illuminate\Support\Collection
    {
        return Item::whereIn('id', $itemIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    /**
     * Untuk kalender interaktif per-barang: kembalikan daftar tanggal
     * yang stoknya sudah habis (0) dalam rentang bulan tertentu.
     * Dipakai untuk mem-blok tanggal di date picker/kalender.
     *
     * TIDAK memakai lock — ini murni untuk TAMPILAN (read-only), bukan bagian
     * dari proses booking, jadi tidak butuh konsistensi seketat itu.
     */
    public function getFullyBookedDates(Item $item, string $rangeStart, string $rangeEnd): array
    {
        $bookedTransactions = Transaction::where('item_id', $item->id)
            ->where('status', 'booked')
            ->overlapping($rangeStart, $rangeEnd)
            ->get(['start_date', 'end_date', 'quantity']);

        $usagePerDate = [];
        $today = now()->toDateString();

        foreach ($bookedTransactions as $transaction) {
            $isOverdue = $transaction->end_date->toDateString() < $today;
            $periodEnd = $isOverdue ? $rangeEnd : $transaction->end_date;

            $period = CarbonPeriod::create($transaction->start_date, $periodEnd);

            foreach ($period as $date) {
                $key = $date->toDateString();
                $usagePerDate[$key] = ($usagePerDate[$key] ?? 0) + $transaction->quantity;
            }
        }

        $fullyBooked = [];

        foreach ($usagePerDate as $date => $usage) {
            if ($usage >= $item->total_stock) {
                $fullyBooked[] = $date;
            }
        }

        return $fullyBooked;
    }
}
