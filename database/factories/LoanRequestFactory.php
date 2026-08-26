<?php

namespace Database\Factories;

use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanRequest>
 */
class LoanRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 week');

        return [
            'user_id' => User::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => (clone $startDate)->modify('+3 days')->format('Y-m-d'),
            'purpose' => $this->faker->sentence(),
            'document_path' => 'documents/dummy-test-document.pdf',
        ];
    }
}
