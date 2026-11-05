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
     * Every face of a double-faced card, each with its own image; empty for regular cards.
     *
     * @return list<CardFace>
     */
    public function faces(array $card): array
    {
        return [];
    }

    abstract public function type(array $card): string;

    abstract public function subtype(array $card): string;

    /**
     * The card's type as one line, e.g. "Effect Monster — Spellcaster" (shown above the card's name).
     */
    public function typeLine(array $card): string
    {
        return collect([$this->type($card), $this->subtype($card)])
            ->reject(fn (string $part) => $part === '')
            ->implode(' — ');
    }

    /**
     * The mana cost, for games that have one (shown as a stat on the card page).
     */
    public function manaCost(array $card): ?string
    {
        return null;
    }

    abstract public function description(array $card): string;

    /**
     * The two headline stats, e.g. ATK/DEF or Power/Toughness.
     *
     * @return array{left: array{label: string, value: mixed}, right: array{label: string, value: mixed}}|array{}
     */
    abstract public function stats(array $card): array;

    abstract public function setName(array $card): ?string;

    abstract public function rarity(array $card): ?string;

    /**
     * Whether the card gets the rainbow foil sheen on the card page: its rarity contains one of the series'
     * `holo_rarities` words, ignoring case. Series whose foil isn't a rarity override this.
     *
     * @param  list<string>  $holoRarities
     */
    public function isHolo(array $card, array $holoRarities): bool
    {
        $rarity = (string) $this->rarity($card);

        return $rarity !== '' && collect($holoRarities)->contains(fn (string $word) => stripos($rarity, $word) !== false);
    }

    abstract public function price(array $card): ?string;

    /**
     * The price as a number in the app's currency (`config('app.currency')`), or null when the card has none.
     */
    public function priceValue(array $card): ?float
    {
        $price = $this->price($card);

        return is_numeric($price) ? (float) $price : null;
    }

    /**
     * What counts as "the same card" in a collection: every printing of a card shares it, so a deck needing 4 is
     * covered by 4 copies of any printing. Falls back to the card id.
     */
    abstract public function collectionKey(array $card): string;

    /**
     * The printing a collection records when the card is added from a set's page (e.g. "LOB-EN005", or a Magic
     * printing id), or '' when the card has none in that set.
     */
    abstract public function printingIn(array $card, string $setCode, ?string $setName = null): string;

    /**
     * The most copies of a card allowed in a format (a key of the series' `deck.formats`): 0 when the card is
     * banned or not legal there, or null when the format adds no limit beyond the normal deck rules.
     */
    abstract public function copyLimit(array $card, string $format): ?int;

    /**
     * The deck stats groups a card falls into, as group key => value, e.g. ['category' => 'Monster', 'level' => 'Level 4'].
     * Keys are groups in the series' `deck.stats`; groups that don't apply to the card are left out.
     *
     * @return array<string, string>
     */
    abstract public function statFacets(array $card): array;
}
