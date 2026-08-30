<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Transaction;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityBulkStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_stock_calculation_matches_individual_calculation(): void
    {
        $availability = app(AvailabilityService::class);

        // 3 barang dengan kondisi berbeda-beda: penuh terbooking, sebagian
        // terbooking, dan tidak ada transaksi sama sekali — supaya method
        // getAvailableStockBulk() dites lengkap dari beberapa skenario, bukan
        // cuma 1 kasus paling gampang.
        $itemFull = Item::factory()->create(['total_stock' => 2]);
        $itemPartial = Item::factory()->create(['total_stock' => 5]);
        $itemEmpty = Item::factory()->create(['total_stock' => 3]);

        Transaction::factory()->create([
            'item_id' => $itemFull->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-05',
            'quantity' => 2,
            'status' => 'booked',
        ]);

        Transaction::factory()->create([
            'item_id' => $itemPartial->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-05',
            'quantity' => 2,
            'status' => 'booked',
        ]);

        // Transaksi 'pending' SENGAJA tidak boleh ikut mengurangi stok —
        // ini juga sekalian mengetes bahwa bulk method tidak salah hitung
        // status yang tidak relevan.
        Transaction::factory()->create([
            'item_id' => $itemEmpty->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-05',
            'quantity' => 99,
            'status' => 'pending',
        ]);

        $items = collect([$itemFull, $itemPartial, $itemEmpty]);

        $bulkResult = $availability->getAvailableStockBulk($items, '2026-09-02', '2026-09-03');

        foreach ($items as $item) {
            $individualResult = $availability->getAvailableStock($item, '2026-09-02', '2026-09-03');

            $this->assertSame(
                $individualResult,
                $bulkResult[$item->id],
                "Hasil bulk untuk item #{$item->id} ({$item->name}) tidak cocok dengan hasil individual."
            );
        }

        // Nilai konkret, supaya kalau ada bug logika (bukan cuma beda cara
        // hitung), test ini tetap menangkapnya secara eksplisit.
        $this->assertSame(0, $bulkResult[$itemFull->id]);   // 2 - 2 = 0
        $this->assertSame(3, $bulkResult[$itemPartial->id]); // 5 - 2 = 3
        $this->assertSame(3, $bulkResult[$itemEmpty->id]);   // 3 - 0 (pending diabaikan)
    }

    public function test_bulk_stock_calculation_returns_empty_array_for_empty_input(): void
    {
        $availability = app(AvailabilityService::class);

        $result = $availability->getAvailableStockBulk(collect(), '2026-09-01', '2026-09-05');

        $this->assertSame([], $result);
    }
}
