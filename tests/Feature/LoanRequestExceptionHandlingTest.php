<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoanRequestExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_unavailable_exception_shows_specific_message_and_cleans_up_document(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 1]);

        $startDate = now()->addDays(5)->toDateString();
        $endDate = now()->addDays(10)->toDateString();

        // Habiskan stok satu-satunya barang ini untuk rentang tanggal yang sama.
        Transaction::factory()->create([
            'item_id' => $item->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantity' => 1,
            'status' => 'booked',
        ]);

        $document = UploadedFile::fake()->create('surat-tugas.pdf', 500);

        $response = $this->actingAs($user)
            ->withSession(['loan_cart' => [$item->id => 1]])
            ->post(route('loan-requests.store'), [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'purpose' => 'Keperluan tugas kuliah',
                'document' => $document,
            ]);

        $response->assertSessionHasErrors('cart');
        $errorMessage = session('errors')->get('cart')[0];
        $this->assertStringContainsString($item->name, $errorMessage);
        $this->assertStringContainsString('tidak mencukupi', $errorMessage);

        $uploadedFiles = Storage::disk('public')->allFiles('documents');
        $this->assertEmpty($uploadedFiles);
    }

    public function test_unexpected_system_exception_logs_error_and_shows_generic_message(): void
    {
        Storage::fake('public');
        Log::spy();

        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);

        $this->app->instance(AvailabilityService::class, new class extends AvailabilityService
        {
            public function lockItems(array $itemIds): \Illuminate\Support\Collection
            {
                throw new \RuntimeException('Simulasi: koneksi database terputus saat lockForUpdate.');
            }
        });

        $document = UploadedFile::fake()->create('surat-tugas.pdf', 500);

        $response = $this->actingAs($user)
            ->withSession(['loan_cart' => [$item->id => 1]])
            ->post(route('loan-requests.store'), [
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'purpose' => 'Keperluan tugas kuliah',
                'document' => $document,
            ]);

        $response->assertSessionHasErrors('cart');
        $errorMessage = session('errors')->get('cart')[0];
        $this->assertStringNotContainsString('koneksi database', $errorMessage);
        $this->assertStringContainsString('Terjadi kesalahan', $errorMessage);

        Log::shouldHaveReceived('error')->atLeast()->once();

        $uploadedFiles = Storage::disk('public')->allFiles('documents');
        $this->assertEmpty($uploadedFiles);
    }
}
