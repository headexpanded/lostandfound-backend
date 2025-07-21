<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LostItem>
 */
class LostItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'location' => $this->faker->address(),
            'backstory' => $this->faker->optional()->paragraph(),
            'keywords' => $this->faker->optional()->randomElements(['keys', 'phone', 'wallet', 'bicycle', 'dog', 'cat', 'book', 'laptop'], $this->faker->numberBetween(1, 3)),
            'status' => $this->faker->randomElement(['active', 'found', 'expired']),
            'fee_paid' => $this->faker->randomFloat(2, 1, 20),
            'lost_date' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
