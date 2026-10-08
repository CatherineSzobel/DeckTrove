<?php

namespace App\Cards;

/**
 * Magic: The Gathering cards in the Scryfall format.
 *
 * Double-faced cards keep most fields per face (`card_faces`) instead of at the top level.
 */
class MagicCardMapper extends CardMapper
{
    /** Scryfall legalities that limit copies; `legal` adds no limit. */
    private const LEGALITY_LIMITS = ['banned' => 0, 'not_legal' => 0, 'restricted' => 1];

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

    public function price(array $card): ?string
    {
        return data_get($card, 'prices.usd');
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
     * A top-level field, or the faces' values joined with " // " for double-faced cards.
     */
    private function field(array $card, string $field): string
    {
        return $card[$field]
            ?? collect($card['card_faces'] ?? [])->pluck($field)->filter()->implode(' // ');
    }
}
