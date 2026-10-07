<?php

namespace Database\Factories;

use App\Models\YugiohCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\YugiohCard>
 */
class YugiohCardFactory extends Factory
{
    public function definition(): array
    {
        return YugiohCard::attributesFrom($this->card());
    }

    /**
     * A raw card in the YGOPRODeck format, with optional overrides.
     */
    public function card(array $overrides = []): array
    {
        $id = $overrides['id'] ?? fake()->unique()->numberBetween(10000000, 99999999);

        return array_merge([
            'id' => $id,
            'name' => fake()->unique()->words(3, true),
            'type' => 'Normal Monster',
            'desc' => fake()->sentence(),
            'race' => 'Spellcaster',
            'atk' => 2500,
            'def' => 2100,
            'level' => 7,
            'attribute' => 'DARK',
            'card_sets' => [['set_name' => 'Legend of Blue Eyes White Dragon', 'set_code' => 'LOB-EN005', 'set_rarity' => 'Ultra Rare']],
            'card_images' => [['image_url' => "https://images.ygoprodeck.com/images/cards/$id.jpg", 'image_url_small' => "https://images.ygoprodeck.com/images/cards_small/$id.jpg"]],
            'card_prices' => [['cardmarket_price' => '0.10']],
        ], $overrides);
    }

    public function fromCard(array $overrides): static
    {
        return $this->state(fn () => YugiohCard::attributesFrom($this->card($overrides)));
    }
}
