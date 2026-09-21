<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SessionCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_prefill_dates_is_cleared_after_successful_checkout(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);

        $startDate = now()->addDays(5)->toDateString();
        $endDate = now()->addDays(10)->toDateString();

        $response = $this->actingAs($user)
            ->withSession([
                'loan_cart' => [$item->id => 1],
                'loan_prefill_dates' => ['start_date' => $startDate, 'end_date' => $endDate],
            ])
            ->post(route('loan-requests.store'), [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'purpose' => 'Keperluan tugas kuliah',
                'document' => UploadedFile::fake()->create('surat-tugas.pdf', 500),
            ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertNull(session('loan_cart'));
        $this->assertNull(session('loan_prefill_dates'));
    }

    public function test_loan_prefill_dates_is_cleared_when_cart_becomes_empty_via_remove(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->withSession([
                'loan_cart' => [$item->id => 1],
                'loan_prefill_dates' => ['start_date' => '2026-09-20', 'end_date' => '2026-09-25'],
            ])
            ->delete(route('loan-cart.remove', $item));

        $response->assertSessionDoesntHaveErrors();
        $this->assertNull(session('loan_prefill_dates'));
        $this->assertNull(session('loan_cart'));
    }

    public function test_loan_prefill_dates_is_kept_when_cart_still_has_other_items(): void
    {
        $user = User::factory()->create();
        $itemToRemove = Item::factory()->create();
        $itemToKeep = Item::factory()->create();

        $this->actingAs($user)
            ->withSession([
                'loan_cart' => [$itemToRemove->id => 1, $itemToKeep->id => 2],
                'loan_prefill_dates' => ['start_date' => '2026-09-20', 'end_date' => '2026-09-25'],
            ])
            ->delete(route('loan-cart.remove', $itemToRemove));

        // Keranjang masih ada isinya (itemToKeep) — tanggal prefill TIDAK
        // boleh ikut hilang, karena user mungkin masih lanjut checkout.
        $this->assertNotNull(session('loan_prefill_dates'));
        $this->assertEquals([$itemToKeep->id => 2], session('loan_cart'));
    }
}
