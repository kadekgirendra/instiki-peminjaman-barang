<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessLogicValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_hits_rate_limiter_only_once_per_attempt(): void
    {
        RateLimiter::clear('john|127.0.0.1');

        $user = User::factory()->create([
            'username' => 'john',
            'password' => 'password123',
        ]);

        // Coba login salah 4 kali berturut-turut.
        // Dengan batas 5 percobaan, percobaan ke-4 masih HARUS menghasilkan error validasi password biasa,
        // BUKAN error lockout "Terlalu banyak percobaan".
        // (Jika bug hit 2x terjadi, percobaan ke-3 sudah lockout).
        for ($i = 1; $i <= 4; $i++) {
            $response = $this->post(route('login'), [
                'username' => 'john',
                'password' => 'wrong-password',
            ]);
            $response->assertSessionHasErrors(['username' => 'Username atau password salah.']);
        }

        // Percobaan ke-5 yang salah
        $response5 = $this->post(route('login'), [
            'username' => 'john',
            'password' => 'wrong-password',
        ]);
        $response5->assertSessionHasErrors(['username' => 'Username atau password salah.']);

        // Percobaan ke-6 harus kena rate limited (lockout)
        $response6 = $this->post(route('login'), [
            'username' => 'john',
            'password' => 'wrong-password',
        ]);
        $response6->assertSessionHasErrors('username');
        $this->assertStringContainsString('Terlalu banyak percobaan login', session('errors')->first('username'));
    }

    public function test_item_show_page_calculates_stock_based_on_selected_dates(): void
    {
        $this->withoutVite();

        /** @var User $user */
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);

        // 3 unit sudah ter-booked untuk rentang tanggal 2026-10-01 s/d 2026-10-05
        Transaction::factory()->create([
            'item_id' => $item->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'quantity' => 3,
            'status' => 'booked',
        ]);

        // Tanpa filter tanggal: ketersediaan = 5 (total_stock)
        $this->actingAs($user)
            ->get(route('items.show', $item))
            ->assertOk()
            ->assertViewHas('availableStock', 5);

        // Dengan filter tanggal yang overlap: ketersediaan = 5 - 3 = 2
        $this->actingAs($user)
            ->get(route('items.show', $item).'?start_date=2026-10-02&end_date=2026-10-04')
            ->assertOk()
            ->assertViewHas('availableStock', 2);
    }

    public function test_cannot_add_soft_deleted_item_to_cart(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $item->delete();

        $response = $this->actingAs($user)->post(route('loan-cart.add'), [
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('item_id');
        $this->assertEmpty(session('loan_cart', []));
    }

    public function test_prefill_dates_cleared_when_cart_becomes_empty(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);

        $this->actingAs($user)
            ->withSession([
                'loan_cart' => [$item->id => 1],
                'loan_prefill_dates' => ['start_date' => '2026-10-01', 'end_date' => '2026-10-05'],
            ])
            ->delete(route('loan-cart.remove', $item));

        $this->assertArrayNotHasKey('loan_cart', session()->all());
        $this->assertArrayNotHasKey('loan_prefill_dates', session()->all());
    }

    public function test_prefill_dates_cleared_on_successful_loan_request(): void
    {
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);
        $document = UploadedFile::fake()->create('ktm.pdf', 100);

        $response = $this->actingAs($user)
            ->withSession([
                'loan_cart' => [$item->id => 2],
                'loan_prefill_dates' => ['start_date' => now()->toDateString(), 'end_date' => now()->addDays(2)->toDateString()],
            ])
            ->post(route('loan-requests.store'), [
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'purpose' => 'Praktikum',
                'document' => $document,
            ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertArrayNotHasKey('loan_cart', session()->all());
        $this->assertArrayNotHasKey('loan_prefill_dates', session()->all());
    }

    public function test_unexpected_exception_during_loan_request_reports_error_and_cleans_up_document(): void
    {
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);
        $document = UploadedFile::fake()->create('ktm.pdf', 100);

        $mockAvailability = \Mockery::mock(AvailabilityService::class);
        $mockAvailability->shouldReceive('lockItems')
            ->once()
            ->andThrow(new \PDOException('Deadlock simulated'));
        $this->app->instance(AvailabilityService::class, $mockAvailability);

        $response = $this->actingAs($user)
            ->withSession([
                'loan_cart' => [$item->id => 1],
            ])
            ->post(route('loan-requests.store'), [
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'purpose' => 'Praktikum',
                'document' => $document,
            ]);

        $response->assertSessionHasErrors(['cart' => 'Terjadi kesalahan saat memproses pengajuan. Silakan coba lagi.']);

        // File dokumen upload harus tetap bersih dari storage (tidak jadi file sampah)
        $this->assertEmpty(Storage::disk('public')->files('documents'));
    }
}
