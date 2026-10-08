<?php

namespace App\Decks;

/**
 * The result of parsing a deck list.
 */
final class ImportedDeck
{
    /**
     * @param  array<string, array<string, int>>  $counts  card counts per zone, keyed by external id
     * @param  list<string>  $missing  lines or ids that didn't match a card
     */
    public function __construct(
        public readonly array $counts,
        public readonly array $missing,
    ) {}

    public function isEmpty(): bool
    {
        return array_sum(array_map('array_sum', $this->counts)) === 0;
    }
}
