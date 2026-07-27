<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->text(),
            'user_id' => fake()->text(),
            'item_id' => fake()->text(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'purpose' => $this->faker->paragraph(),
            'quantity' => fake()->text(),
            'status' => fake()->text(),
            'total_fee' => fake()->text(),
            'document_path' => fake()->text(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
