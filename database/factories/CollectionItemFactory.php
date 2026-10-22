<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionItem>
 */
class CollectionItemFactory extends Factory
{
    public function definition(): array
    {
        $externalId = (string) fake()->unique()->numberBetween(10000000, 99999999);

        return [
            'user_id' => User::factory(),
            'list' => CollectionItem::OWNED,
            'game' => 'yugioh',
            'card_key' => $externalId,
            'card_id' => fn () => Card::create(['game' => 'yugioh', 'external_id' => $externalId, 'name' => fake()->words(2, true)])->id,
            'printing' => '',
            'quantity' => 1,
        ];
    }
}
