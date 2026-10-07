<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deck>
 */
class DeckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game' => fake()->randomElement(['magic', 'yugioh']),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_public' => false,
            'image' => null,
        ];
    }

    public function public(): static
    {
        return $this->state(fn () => ['is_public' => true]);
    }
}
