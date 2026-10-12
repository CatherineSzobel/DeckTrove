<?php

namespace Database\Factories;

use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeckLike>
 */
class DeckLikeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'deck_id' => Deck::factory()->public(),
        ];
    }
}
