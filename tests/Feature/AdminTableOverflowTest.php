<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTableOverflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_transactions_index_table_container_has_overflow_x_auto(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.transactions.index'));

        $response->assertOk();
        $response->assertSee('overflow-x-auto');
    }

    public function test_admin_users_index_table_container_has_overflow_x_auto(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('overflow-x-auto');
    }

    public function test_admin_users_show_rekap_table_container_has_overflow_x_auto(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $loanRequest = LoanRequest::factory()->create(['user_id' => $user->id]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'loan_request_id' => $loanRequest->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $user));

        $response->assertOk();
        $response->assertSee('overflow-x-auto');
    }
}
