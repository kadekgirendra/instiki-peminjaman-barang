<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_only_increments_rate_limiter_once_per_attempt(): void
    {
        $user = User::factory()->create(['username' => 'testuser']);

        $throttleKey = 'testuser|127.0.0.1';

        for ($i = 1; $i <= 4; $i++) {
            $this->post(route('login'), [
                'username' => 'testuser',
                'password' => 'password-salah',
            ]);

            // Setelah percobaan ke-N yang gagal, jumlah hit yang tercatat
            // HARUS persis N — kalau bug double-hit masih ada, di sini akan
            // muncul 2N (misal 8 setelah 4 percobaan, bukan 4).
            $this->assertEquals(
                $i,
                RateLimiter::attempts($throttleKey),
                "Setelah {$i} percobaan gagal, jumlah hit rate limiter harus tepat {$i}."
            );
        }
    }

    public function test_user_is_not_locked_out_before_five_failed_attempts(): void
    {
        User::factory()->create(['username' => 'testuser2']);

        // 4 percobaan gagal berturut-turut TIDAK BOLEH memicu pesan
        // rate-limit — kalau bug double-hit masih ada, percobaan ke-3
        // sudah akan ter-lockout (karena counter sudah mencapai 6/5).
        for ($i = 1; $i <= 4; $i++) {
            $response = $this->post(route('login'), [
                'username' => 'testuser2',
                'password' => 'password-salah',
            ]);

            $response->assertSessionHasErrors('username');
            $message = session('errors')->get('username')[0];

            $this->assertStringNotContainsString(
                'Terlalu banyak percobaan',
                $message,
                "Percobaan ke-{$i} seharusnya belum kena rate limit."
            );
        }
    }

    public function test_user_is_locked_out_after_five_failed_attempts(): void
    {
        User::factory()->create(['username' => 'testuser3']);

        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('login'), [
                'username' => 'testuser3',
                'password' => 'password-salah',
            ]);
        }

        // Percobaan ke-6 BARU boleh kena rate limit.
        $response = $this->post(route('login'), [
            'username' => 'testuser3',
            'password' => 'password-salah',
        ]);

        $response->assertSessionHasErrors('username');
        $message = session('errors')->get('username')[0];
        $this->assertStringContainsString('Terlalu banyak percobaan', $message);
    }
}
