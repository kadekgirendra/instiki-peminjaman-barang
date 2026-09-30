<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LoanRequestDurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_request_rejects_duration_longer_than_seven_days(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'total_stock' => 5,
        ]);

        session([
            'loan_cart' => [
                $item->id => 1,
            ],
        ]);

        $response = $this->actingAs($user)->post(
            route('loan-requests.store'),
            [
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(13)->toDateString(),
                'purpose' => 'Tes durasi',
                'document' => UploadedFile::fake()->create(
                    'dokumen.pdf',
                    500
                ),
            ]
        );

        $response->assertSessionHasErrors('end_date');
    }

    public function test_loan_request_accepts_duration_exactly_seven_days(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'total_stock' => 5,
        ]);

        session([
            'loan_cart' => [
                $item->id => 1,
            ],
        ]);

        $response = $this->actingAs($user)->post(
            route('loan-requests.store'),
            [
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(12)->toDateString(),
                'purpose' => 'Tes durasi',
                'document' => UploadedFile::fake()->create(
                    'dokumen.pdf',
                    500
                ),
            ]
        );

        $response->assertSessionDoesntHaveErrors('end_date');
    }
}
