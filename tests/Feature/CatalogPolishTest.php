<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPolishTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ── items/index ──

    public function test_catalog_uses_indonesian_labels(): void
    {
        Item::factory()->create(['total_stock' => 3]);

        $response = $this->actingAs($this->user)->get(route('items.index'));
        $response->assertOk();

        // Search placeholder
        $response->assertSee('Cari barang...', false);
        $response->assertDontSee('Search items...', false);

        // Category dropdown — both desktop and mobile
        $response->assertSee('Semua Kategori', false);
        $response->assertDontSee('All Categories', false);

        // Stock label
        $response->assertSee('Stok:', false);
        $response->assertDontSee('Stock:', false);
    }

    public function test_catalog_card_has_hover_effect(): void
    {
        Item::factory()->create(['total_stock' => 1]);

        $response = $this->actingAs($this->user)->get(route('items.index'));

        $response->assertSee('hover:shadow-md', false);
        $response->assertSee('hover:-translate-y-0.5', false);
    }

    public function test_catalog_cta_uses_primary_dark_hover(): void
    {
        Item::factory()->create(['total_stock' => 1]);

        $response = $this->actingAs($this->user)->get(route('items.index'));

        $response->assertSee('hover:bg-primary-dark', false);
    }

    public function test_catalog_image_placeholder_has_svg(): void
    {
        Item::factory()->create(['image' => null, 'total_stock' => 1]);

        $response = $this->actingAs($this->user)->get(route('items.index'));

        // Should have an SVG icon instead of just plain text
        $response->assertSee('w-10 h-10 text-slate-300', false);
        $response->assertSee('Tidak ada foto', false);
    }

    public function test_catalog_empty_state_has_icon_and_cta(): void
    {
        $response = $this->actingAs($this->user)->get(
            route('items.index', ['search' => 'xyznonexistent999'])
        );

        $response->assertOk();

        $response->assertSee('Barang tidak ditemukan', false);
        $response->assertSee('Coba ubah filter atau kata kunci pencarian', false);
        $response->assertSee('Reset pencarian', false);
    }

    // ── items/show ──

    public function test_item_show_stock_meter_has_aria(): void
    {
        $item = Item::factory()->create(['total_stock' => 3]);

        $response = $this->actingAs($this->user)->get(route('items.show', $item));
        $response->assertOk();

        $response->assertSee('role="meter"', false);
        $response->assertSee('aria-label="Ketersediaan stok:', false);
        $response->assertSee('aria-hidden="true"', false);
    }

    public function test_item_show_cta_uses_primary_dark_hover(): void
    {
        $item = Item::factory()->create(['total_stock' => 1]);

        $response = $this->actingAs($this->user)->get(route('items.show', $item));

        $response->assertSee('hover:bg-primary-dark', false);
    }

    public function test_item_show_image_placeholder_has_svg(): void
    {
        $item = Item::factory()->create(['image' => null, 'total_stock' => 1]);

        $response = $this->actingAs($this->user)->get(route('items.show', $item));

        $response->assertSee('w-12 h-12 text-slate-300', false);
        $response->assertSee('Tidak ada foto', false);
    }
}
