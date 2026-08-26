<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Transaction;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AvailabilityRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_locked_check_prevents_overbooking_when_run_sequentially_inside_transactions(): void
    {
        $item = Item::factory()->create(['total_stock' => 1]);
        $availability = app(AvailabilityService::class);

        // Simulasikan 2 "request" yang berebut 1 unit stok terakhir yang sama,
        // dijalankan berurutan tapi masing-masing di dalam transaction sendiri
        // dengan lock — ini membuktikan permintaan KEDUA akan gagal karena
        // permintaan PERTAMA sudah mengunci & mengonsumsi stoknya duluan.
        $firstSucceeded = DB::transaction(function () use ($availability, $item) {
            $locked = $availability->lockItems([$item->id]);
            $ok = $availability->isAvailable($locked[$item->id], '2026-09-01', '2026-09-05', 1, lock: true);

            if ($ok) {
                Transaction::factory()->create([
                    'item_id' => $item->id,
                    'start_date' => '2026-09-01',
                    'end_date' => '2026-09-05',
                    'quantity' => 1,
                    'status' => 'booked',
                ]);
            }

            return $ok;
        });

        $secondSucceeded = DB::transaction(function () use ($availability, $item) {
            $locked = $availability->lockItems([$item->id]);
            return $availability->isAvailable($locked[$item->id], '2026-09-02', '2026-09-04', 1, lock: true);
        });

        $this->assertTrue($firstSucceeded, 'Permintaan pertama seharusnya berhasil (stok masih ada).');
        $this->assertFalse($secondSucceeded, 'Permintaan kedua seharusnya GAGAL (stok sudah habis oleh permintaan pertama).');
    }
}
