<?php

namespace Database\Factories;

use App\Models\YugiohSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YugiohSet>
 */
class YugiohSetFactory extends Factory
{
    public function definition(): array
    {
        return YugiohSet::attributesFrom([
            'set_name' => fake()->unique()->words(3, true),
            'set_code' => strtoupper(fake()->unique()->lexify('???')),
            'num_of_cards' => fake()->numberBetween(5, 120),
            'tcg_date' => fake()->date(),
        ]);
    }
}
