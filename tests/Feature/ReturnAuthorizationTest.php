<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_open_return_form_for_someone_elses_loan_request(): void
    {
        $this->withoutVite();

        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $item = Item::factory()->create(['total_stock' => 1]);
        $loanRequest = LoanRequest::create([
            'user_id' => $owner->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'purpose' => 'test',
            'document_path' => 'documents/dummy.pdf',
        ]);
        Transaction::factory()->create([
            'loan_request_id' => $loanRequest->id,
            'user_id' => $owner->id,
            'item_id' => $item->id,
            'status' => 'booked',
        ]);

        $this->actingAs($stranger)
            ->get(route('returns.create', $loanRequest))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('returns.create', $loanRequest))
            ->assertOk();
    }
}
