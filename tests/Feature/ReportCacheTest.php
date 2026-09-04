<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ReportCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_caches_results_per_filter_combination(): void
    {
        Cache::flush();

        $admin = User::factory()->admin()->create();
        $item = Item::factory()->create();
        $loanRequest = LoanRequest::factory()->create();
        Transaction::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'item_id' => $item->id,
            'status' => 'booked',
            'quantity' => 1,
        ]);

        // Buka halaman laporan dengan filter 'all' — ini akan mengisi cache.
        $this->actingAs($admin)->get(route('admin.reports.index', ['range' => 'all', 'category' => 'all']))
            ->assertOk();

        $this->assertTrue(
            Cache::has('report:item-rows:null:null:all'),
            'Cache laporan seharusnya sudah terisi setelah halaman dibuka.'
        );

        // Hapus data transaksi langsung dari database (tanpa lewat cache-busting
        // apapun) — kalau cache BENERAN dipakai, laporan kedua ini harus tetap
        // menunjukkan data LAMA (dari cache), bukan data baru yang sudah kosong.
        Transaction::query()->delete();

        $secondView = $this->actingAs($admin)->get(route('admin.reports.index', ['range' => 'all', 'category' => 'all']));
        $secondView->assertOk();
        $secondView->assertViewHas('itemRows', function ($itemRows) {
            return $itemRows->isNotEmpty();
        });
    }

    public function test_different_filters_use_different_cache_keys(): void
    {
        Cache::flush();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.reports.index', ['range' => '7d', 'category' => 'all']))
            ->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.index', ['range' => '30d', 'category' => 'all']))
            ->assertOk();

        // Filter berbeda (7d vs 30d) harus punya cache key TERPISAH — kalau
        // key-nya kebentur/sama, ini akan gagal karena salah satu tidak ada.
        $this->assertNotEquals(
            Cache::get('report:summary:'.now()->subDays(7)->startOfDay()->toDateString().':'.now()->endOfDay()->toDateString().':all'),
            null
        );
    }
}
