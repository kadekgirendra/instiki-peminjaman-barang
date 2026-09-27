<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        $response->assertSessionHasErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_delete_another_admin_account(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $response = $this->actingAs($admin1)->delete(route('admin.users.destroy', $admin2));

        $response->assertSessionHasErrors(['error' => 'Akun administrator tidak dapat dihapus.']);
        $this->assertNotSoftDeleted('users', ['id' => $admin2->id]);
    }

    public function test_admin_cannot_view_admin_account_detail(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $response = $this->actingAs($admin1)->get(route('admin.users.show', $admin2));

        $response->assertNotFound();
    }

    public function test_admin_cannot_access_edit_form_for_admin_account(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $response = $this->actingAs($admin1)->get(route('admin.users.edit', $admin2));

        $response->assertNotFound();
    }

    public function test_admin_cannot_update_admin_account(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create(['name' => 'Original Admin Name']);

        $response = $this->actingAs($admin1)->put(route('admin.users.update', $admin2), [
            'username' => 'modified_admin',
            'name' => 'Modified Name',
            'nim_nidn' => '1234567890',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('users', [
            'id' => $admin2->id,
            'name' => 'Original Admin Name',
        ]);
    }

    public function test_admin_can_delete_regular_user_without_active_transactions(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'User berhasil dihapus.');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
