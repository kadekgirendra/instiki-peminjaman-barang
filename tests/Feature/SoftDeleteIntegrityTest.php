<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SoftDeleteIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_retains_item_and_user_after_soft_delete(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['total_stock' => 5]);
        $loanRequest = LoanRequest::factory()->create(['user_id' => $user->id]);

        $trx = Transaction::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'user_id' => $user->id,
            'item_id' => $item->id,
            'status' => 'completed',
        ]);

        $item->delete();
        $user->delete();

        $trx->refresh();

        $this->assertNotNull($trx->item);
        $this->assertSame($item->name, $trx->item->name);

        $this->assertNotNull($trx->user);
        $this->assertSame($user->name, $trx->user->name);

        $loanRequest->refresh();
        $this->assertNotNull($loanRequest->user);
        $this->assertSame($user->name, $loanRequest->user->name);
    }

    public function test_soft_deleting_item_does_not_delete_its_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $item = Item::factory()->create([
            'image' => 'items/foto-arsip.jpg',
        ]);
        Storage::disk('public')->put('items/foto-arsip.jpg', 'foto barang');

        $response = $this->actingAs($admin)->delete(route('admin.items.destroy', $item));
        $response->assertRedirect(route('admin.items.index'));

        $this->assertSoftDeleted('items', ['id' => $item->id]);
        Storage::disk('public')->assertExists('items/foto-arsip.jpg');
    }

    public function test_cannot_delete_item_with_active_or_pending_transactions(): void
    {
        $admin = User::factory()->admin()->create();
        $item = Item::factory()->create();

        Transaction::factory()->create([
            'item_id' => $item->id,
            'status' => 'booked',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.items.destroy', $item));
        $response->assertSessionHasErrors('error');
        $this->assertNotSoftDeleted('items', ['id' => $item->id]);
    }

    public function test_cannot_delete_user_with_active_or_pending_transactions(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $user));
        $response->assertSessionHasErrors('error');
        $this->assertNotSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_admin_pages_render_without_error_when_items_or_users_are_soft_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $loanRequest = LoanRequest::factory()->create(['user_id' => $user->id]);
        Transaction::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'user_id' => $user->id,
            'item_id' => $item->id,
            'status' => 'completed',
        ]);

        $item->delete();
        $user->delete();

        $this->actingAs($admin)->get(route('admin.transactions.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }
}
