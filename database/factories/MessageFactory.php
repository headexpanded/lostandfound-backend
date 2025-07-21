<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_user_id' => \App\Models\User::factory(),
            'to_user_id' => \App\Models\User::factory(),
            'lost_item_id' => \App\Models\LostItem::factory(),
            'message' => $this->faker->paragraph(),
            'read' => $this->faker->boolean(20), // 20% chance of being read
        ];
    }
}
