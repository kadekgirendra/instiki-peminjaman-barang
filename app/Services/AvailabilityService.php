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
     */
    public function getBookedQuantity(Item $item, string $startDate, string $endDate): int
    {
        return Transaction::where('item_id', $item->id)
            ->where('status', 'booked')
            ->overlapping($startDate, $endDate)
            ->sum('quantity');
    }

     /**
     * Stok aktual = Total Stok - Jumlah yang sudah booked di rentang tanggal itu.
     */
    public function getAvailableStock(Item $item, string $startDate, string $endDate): int
    {
        $booked = $this->getBookedQuantity($item, $startDate, $endDate);

        return max($item->total_stock - $booked, 0);
    }

    /**
     * Cek apakah item tersedia untuk quantity tertentu di rentang tanggal tersebut.
     */
    public function isAvailable(Item $item, string $startDate, string $endDate, int $quantity = 1): bool
    {
        return $this->getAvailableStock($item, $startDate, $endDate) >= $quantity;
    }

    /**
     * Untuk kalender interaktif per-barang: kembalikan daftar tanggal
     * yang stoknya sudah habis (0) dalam rentang bulan tertentu.
     * Dipakai untuk mem-blok tanggal di date picker/kalender.
     */
    public function getFullyBookedDates(Item $item, string $rangeStart, string $rangeEnd): array
    {
        $bookedTransactions = Transaction::where('item_id', $item->id)
            ->where('status', 'booked')
            ->overlapping($rangeStart, $rangeEnd)
            ->get(['start_date', 'end_date', 'quantity']);

        // Hitung total quantity terpakai per tanggal
        $usagePerDate = [];

        foreach ($bookedTransactions as $transaction) {
            $period = CarbonPeriod::create($transaction->start_date, $transaction->end_date);

            foreach ($period as $date) {
                $key = $date->toDateString();
                $usagePerDate[$key] = ($usagePerDate[$key] ?? 0) + $transaction->quantity;
            }
        }

        // Tanggal yang penuh = usage >= total_stock
        $fullyBooked = [];

        foreach ($usagePerDate as $date => $usage) {
            if ($usage >= $item->total_stock) {
                $fullyBooked[] = $date;
            }
        }

        return $fullyBooked;
    }

}
