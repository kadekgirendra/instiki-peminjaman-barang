<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminItemEmptyStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_items_index_shows_proper_empty_state_when_no_items_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.items.index'));

        $response->assertOk();
        $response->assertSee('Belum ada barang');
        $response->assertSee(route('admin.items.create'));
        $response->assertDontSee(route('items.index'));
        $response->assertSee('colspan="6"', false);
    }

    public function test_admin_items_index_shows_search_not_found_with_correct_admin_reset_route(): void
    {
        $admin = User::factory()->admin()->create();
        Item::factory()->create(['name' => 'Kamera Canon']);

        $response = $this->actingAs($admin)->get(route('admin.items.index', ['search' => 'nonexistent']));

        $response->assertOk();
        $response->assertSee('Barang tidak ditemukan');
        $response->assertSee(route('admin.items.index'));
        $response->assertDontSee(route('items.index'));
        $response->assertSee('colspan="6"', false);
    }
}
