<?php

namespace Tests\Feature;

use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteAndMarkPaidGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_rejects_when_no_booked_items_exist(): void
    {
        $admin = User::factory()->admin()->create();
        $loanRequest = LoanRequest::factory()->create();
        // Sengaja TIDAK ada transaksi berstatus 'booked' sama sekali.
        Transaction::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(
            route('admin.loan-requests.complete', $loanRequest),
            ['returned_at' => now()->toDateString()]
        );

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('transactions', [
            'loan_request_id' => $loanRequest->id,
            'status' => 'pending', // tidak ikut berubah
        ]);
    }

    public function test_mark_paid_rejects_when_no_completed_items_exist(): void
    {
        $admin = User::factory()->admin()->create();
        $loanRequest = LoanRequest::factory()->create();
        Transaction::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'status' => 'booked', // belum 'completed'
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.loan-requests.mark-paid', $loanRequest));

        $response->assertSessionHasErrors('error');
    }

    public function test_mark_paid_rejects_when_there_is_no_fine_to_pay(): void
    {
        $admin = User::factory()->admin()->create();
        $loanRequest = LoanRequest::factory()->create();
        Transaction::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'status' => 'completed',
            'total_fee' => 0,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.loan-requests.mark-paid', $loanRequest));

        $response->assertSessionHasErrors('error');
    }

    public function test_mark_paid_rejects_when_already_marked_paid_before(): void
    {
        $admin = User::factory()->admin()->create();
        $loanRequest = LoanRequest::factory()->create();
        $originalPaidAt = now()->subDays(3);

        $transaction = Transaction::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'status' => 'completed',
            'total_fee' => 15000,
            'paid_at' => $originalPaidAt,
        ]);

        // Coba tandai lunas LAGI — ini harus ditolak, supaya paid_at asli
        // (bukti kapan sebenarnya pertama kali dibayar) tidak tertimpa.
        $response = $this->actingAs($admin)->patch(route('admin.loan-requests.mark-paid', $loanRequest));

        $response->assertSessionHasErrors('error');

        $transaction->refresh();
        $this->assertEquals(
            $originalPaidAt->toDateTimeString(),
            $transaction->paid_at->toDateTimeString(),
            'paid_at seharusnya TIDAK berubah karena sudah pernah ditandai lunas sebelumnya.'
        );
    }

    public function test_mark_paid_succeeds_for_valid_unpaid_completed_transaction(): void
    {
        $admin = User::factory()->admin()->create();
        $loanRequest = LoanRequest::factory()->create();
        Transaction::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'status' => 'completed',
            'total_fee' => 20000,
            'paid_at' => null,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.loan-requests.mark-paid', $loanRequest));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('transactions', [
            'loan_request_id' => $loanRequest->id,
            'status' => 'completed',
        ]);

        $transaction = Transaction::where('loan_request_id', $loanRequest->id)->first();
        $this->assertNotNull($transaction->paid_at);
    }
}
