<?php

namespace App\Cards;

use Illuminate\Support\Str;

/**
 * Magic: The Gathering cards in the Scryfall format.
 *
 * Double-faced cards keep most fields per face (`card_faces`) instead of at the top level.
 */
class MagicCardMapper extends CardMapper
{
    /** Scryfall legalities that limit copies; `legal` adds no limit. */
    private const LEGALITY_LIMITS = ['banned' => 0, 'not_legal' => 0, 'restricted' => 1];

    /**
     * Card types in the order that picks a non-land card's one type for deck stats, so an Artifact Creature is a
     * Creature. Lands are checked first (see statFacets()).
     */
    private const STAT_TYPES = ['Creature', 'Planeswalker', 'Battle', 'Instant', 'Sorcery', 'Artifact', 'Enchantment'];

    private const COLOR_NAMES = ['W' => 'White', 'U' => 'Blue', 'B' => 'Black', 'R' => 'Red', 'G' => 'Green'];

    public function image(array $card): string
    {
        return data_get($card, 'image_uris.normal')
            ?? data_get($card, 'card_faces.0.image_uris.normal')
            ?? self::PLACEHOLDER_IMAGE;
    }

    public function imageSmall(array $card): string
    {
        return data_get($card, 'image_uris.small')
            ?? data_get($card, 'card_faces.0.image_uris.small')
            ?? $this->image($card);
    }

    public function faceImages(array $card): array
    {
        return collect($card['card_faces'] ?? [])
            ->pluck('image_uris.normal')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Magic shows the mana cost where Yu-Gi-Oh! shows the card type.
     */
    public function type(array $card): string
    {
        return $this->field($card, 'mana_cost');
    }

    public function subtype(array $card): string
    {
        return $card['type_line'] ?? '';
    }

    /**
     * The type line alone: Magic's type() is the mana cost, which the card page shows as a stat.
     */
    public function typeLine(array $card): string
    {
        return $this->subtype($card);
    }

    public function manaCost(array $card): ?string
    {
        $cost = $this->type($card);

        return $cost === '' ? null : $cost;
    }

    public function description(array $card): string
    {
        return $this->field($card, 'oracle_text');
    }

    public function stats(array $card): array
    {
        return [
            'left' => ['label' => 'Power', 'value' => $card['power'] ?? data_get($card, 'card_faces.0.power')],
            'right' => ['label' => 'Toughness', 'value' => $card['toughness'] ?? data_get($card, 'card_faces.0.toughness')],
        ];
    }

    public function setName(array $card): ?string
    {
        return $card['set_name'] ?? null;
    }

    public function rarity(array $card): ?string
    {
        return $card['rarity'] ?? null;
    }

    /**
     * Scryfall's Cardmarket price in euros, the currency the whole app uses.
     */
    public function price(array $card): ?string
    {
        return data_get($card, 'prices.eur');
    }

    public function printSets(array $card): array
    {
        return [[
            'set_code' => $card['set'] ?? '',
            'set_name' => $card['set_name'] ?? 'Unknown',
        ]];
    }

    /**
     * Scryfall gives one legality per format, which already accounts for set rotation (e.g. Standard).
     */
    public function copyLimit(array $card, string $format): ?int
    {
        $legality = data_get($card, "legalities.$format");

        return is_string($legality) ? (self::LEGALITY_LIMITS[$legality] ?? null) : null;
    }

    /**
     * One type per card (from the front face), the mana value with 7+ grouped, and the color. Lands only get a
     * type, so they stay out of the mana curve and the color chart.
     */
    public function statFacets(array $card): array
    {
        $typeLine = Str::before($card['type_line'] ?? data_get($card, 'card_faces.0.type_line') ?? '', ' // ');

        if ($typeLine === '') {
            return [];
        }

        // Only the types before the dash count; subtypes (e.g. "Elf Druid") never decide the type.
        $types = Str::before($typeLine, ' — ');

        // Any land (also Artifact Land or Dryad Arbor's Land Creature) is a Land, so it stays off the curve and colors.
        if (str_contains($types, 'Land')) {
            return ['type' => 'Land'];
        }

        $type = collect(self::STAT_TYPES)->first(fn (string $candidate) => str_contains($types, $candidate));

        $manaValue = isset($card['cmc']) ? (int) $card['cmc'] : null;
        $colors = $card['colors'] ?? collect($card['card_faces'] ?? [])->pluck('colors')->flatten()->unique()->values()->all();

        return array_filter([
            'type' => $type,
            'mana_value' => $manaValue === null ? null : ($manaValue >= 7 ? '7+' : (string) $manaValue),
            'color' => match (count($colors)) {
                0 => 'Colorless',
                1 => self::COLOR_NAMES[$colors[0]] ?? 'Colorless',
                default => 'Multicolor',
            },
        ], fn (?string $value) => $value !== null);
    }

    /**
     * A top-level field, or the faces' values joined with " // " for double-faced cards.
     */
    private function field(array $card, string $field): string
    {
        return $card[$field]
            ?? collect($card['card_faces'] ?? [])->pluck($field)->filter()->implode(' // ');
    }
}
