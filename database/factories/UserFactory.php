<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'name' => fake()->name(),
            'nim_nidn' => fake()->unique()->numerify('##########'),
            // Cukup teks polos di sini — kolom 'password' di Model User sudah
            // di-cast 'hashed', jadi otomatis di-hash Laravel saat disimpan.
            // Tidak perlu manggil Hash::make() manual di factory.
            'password' => 'password',
            'role' => 'user',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State tambahan: buat user dengan role admin.
     * Pemakaian: User::factory()->admin()->create()
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
