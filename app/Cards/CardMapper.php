<?php

namespace App\Cards;

/**
 * Reads one series' raw card data (as returned by its CardProvider) into the fields the app shows.
 *
 * Each series names its mapper in config/series.php (`mapper`). Mappers live in classes rather than
 * config closures so the configuration stays cacheable (`php artisan config:cache`).
 */
abstract class CardMapper
{
    public const PLACEHOLDER_IMAGE = 'https://placehold.co/200x280?text=No+Image';

    abstract public function image(array $card): string;

    public function imageSmall(array $card): string
    {
        return $this->image($card);
    }

    /**
     * Images of every face for double-faced cards; empty for regular cards.
     */
    public function faceImages(array $card): array
    {
        return [];
    }

    abstract public function type(array $card): string;

    abstract public function subtype(array $card): string;

    abstract public function description(array $card): string;

    /**
     * The two headline stats, e.g. ATK/DEF or Power/Toughness.
     *
     * @return array{left: array{label: string, value: mixed}, right: array{label: string, value: mixed}}|array{}
     */
    abstract public function stats(array $card): array;

    abstract public function setName(array $card): ?string;

    abstract public function rarity(array $card): ?string;

    abstract public function price(array $card): ?string;

    /**
     * @return list<array{set_code: string, set_name: string}>
     */
    abstract public function printSets(array $card): array;
}
