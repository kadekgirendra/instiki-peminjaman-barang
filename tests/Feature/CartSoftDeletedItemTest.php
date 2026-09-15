<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartSoftDeletedItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_add_soft_deleted_item_to_cart(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);
        $item->delete(); // soft delete

        $response = $this->actingAs($user)->post(route('loan-cart.add'), [
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('item_id');
        $this->assertNull(session('loan_cart'));
    }

    public function test_can_still_add_active_item_to_cart(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);

        $response = $this->actingAs($user)->post(route('loan-cart.add'), [
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertEquals([$item->id => 1], session('loan_cart'));
    }
}
