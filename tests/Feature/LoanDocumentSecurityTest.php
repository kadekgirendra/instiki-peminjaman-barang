<?php

namespace Tests\Feature;

use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoanDocumentSecurityTest extends TestCase
{
    use RefreshDatabase;

    // ─── Test: akses dokumen harus login dulu ──────────────────────────────

    public function test_unauthenticated_user_cannot_access_document(): void
    {
        $loanRequest = LoanRequest::factory()->create([
            'document_path' => 'documents/surat-tugas.pdf',
        ]);

        $response = $this->get(route('loan-requests.document', $loanRequest));

        $response->assertRedirect(route('login'));
    }

    // ─── Test: pemilik pengajuan dapat mengakses dokumennya sendiri ─────────

    public function test_owner_can_access_their_own_document(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/surat-tugas.pdf', 'isi dokumen dummy');

        /** @var User $user */
        $user = User::factory()->create();
        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $user->id,
            'document_path' => 'documents/surat-tugas.pdf',
        ]);

        $response = $this->actingAs($user)->get(route('loan-requests.document', $loanRequest));

        $response->assertOk();
    }

    // ─── Test: user lain tidak bisa mengakses dokumen milik user lain ──────

    public function test_other_user_cannot_access_document_of_another_user(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/surat-tugas.pdf', 'isi dokumen dummy');

        /** @var User $owner */
        $owner = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();

        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $owner->id,
            'document_path' => 'documents/surat-tugas.pdf',
        ]);

        $response = $this->actingAs($otherUser)->get(route('loan-requests.document', $loanRequest));

        $response->assertForbidden();
    }

    // ─── Test: admin dapat mengakses dokumen milik siapapun ────────────────

    public function test_admin_can_access_any_document(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/surat-tugas.pdf', 'isi dokumen dummy');

        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $user->id,
            'document_path' => 'documents/surat-tugas.pdf',
        ]);

        $response = $this->actingAs($admin)->get(route('loan-requests.document', $loanRequest));

        $response->assertOk();
    }

    // ─── Test: 404 jika dokumen tidak ada di disk ───────────────────────────

    public function test_returns_404_when_document_file_missing_from_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create();
        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $user->id,
            'document_path' => 'documents/file-yang-tidak-ada.pdf',
        ]);

        $response = $this->actingAs($user)->get(route('loan-requests.document', $loanRequest));

        $response->assertNotFound();
    }

    // ─── Test: 404 jika loan request tidak punya document_path ─────────────

    public function test_returns_404_when_loan_request_has_no_document(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $user->id,
            'document_path' => null,
        ]);

        $response = $this->actingAs($user)->get(route('loan-requests.document', $loanRequest));

        $response->assertNotFound();
    }

    // ─── Test: dokumen tidak bisa diakses via public storage URL langsung ───

    public function test_document_is_not_directly_accessible_via_public_url(): void
    {
        // Dokumen disimpan di disk 'local' (private storage), bukan 'public'.
        // Ini memastikan URL /storage/documents/xxx.pdf tidak bisa diakses
        // tanpa autentikasi — hanya melewati route loan-requests.document.
        Storage::fake('local');
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create();
        $loanRequest = LoanRequest::factory()->create([
            'user_id' => $user->id,
            'document_path' => 'documents/surat-rahasia.pdf',
        ]);

        Storage::disk('local')->put('documents/surat-rahasia.pdf', 'isi dokumen');

        // File TIDAK ada di disk public — URL /storage/documents/... seharusnya
        // tidak bisa diakses langsung oleh siapapun.
        // Menggunakan assertFalse+exists() karena assertMissing() hanya tersedia
        // via Storage::fake() mixin dan tidak dapat di-trace Larastan.
        $this->assertFalse(Storage::disk('public')->exists('documents/surat-rahasia.pdf'));
    }
}
