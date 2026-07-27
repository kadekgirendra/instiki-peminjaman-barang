<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['Kamera', 'Proyektor', 'Alat Lab', 'Audio']),
            'description' => fake()->sentence(),
            'total_stock' => fake()->numberBetween(1, 5),
          
        ];
    }
}
