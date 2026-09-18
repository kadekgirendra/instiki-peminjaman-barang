<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemShowAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_full_stock_when_no_date_filter_given(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);

        $response = $this->actingAs($user)->get(route('items.show', $item));

        $response->assertOk();
        $response->assertViewHas('availableStock', 5);
    }

    public function test_show_returns_reduced_stock_when_date_filter_overlaps_booked_transaction(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);

        $startDate = now()->addDays(5)->toDateString();
        $endDate = now()->addDays(10)->toDateString();

        Transaction::factory()->create([
            'item_id' => $item->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantity' => 3,
            'status' => 'booked',
        ]);

        $response = $this->actingAs($user)->get(
            route('items.show', $item) . '?start_date=' . $startDate . '&end_date=' . $endDate
        );

        $response->assertOk();
        // total_stock 5 - 3 yang sudah booked = 2 tersedia, BUKAN 5 statis.
        $response->assertViewHas('availableStock', 2);
    }

    public function test_show_returns_full_stock_when_date_filter_does_not_overlap_booking(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);

        // Booking jauh di masa depan...
        Transaction::factory()->create([
            'item_id' => $item->id,
            'start_date' => now()->addDays(30)->toDateString(),
            'end_date' => now()->addDays(35)->toDateString(),
            'quantity' => 3,
            'status' => 'booked',
        ]);

        // ...tapi user cek tanggal yang TIDAK bersinggungan sama sekali.
        $checkStart = now()->addDays(5)->toDateString();
        $checkEnd = now()->addDays(10)->toDateString();

        $response = $this->actingAs($user)->get(
            route('items.show', $item) . '?start_date=' . $checkStart . '&end_date=' . $checkEnd
        );

        $response->assertOk();
        $response->assertViewHas('availableStock', 5);
    }
}
