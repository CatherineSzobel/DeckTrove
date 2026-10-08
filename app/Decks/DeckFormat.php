<?php

namespace App\Decks;

use Illuminate\Support\Collection;

/**
 * A deck file format other tools understand, used for importing and exporting decks.
 *
 * Each series names its format in config/series.php (`deck_format`).
 */
interface DeckFormat
{
    /**
     * File extension for exported decks, e.g. "ydk".
     */
    public function extension(): string;

    /**
     * Short help text for the import form, e.g. which tools produce this format.
     */
    public function importHint(): string;

    /**
     * @param  array<string, array<string, int>>  $zones  card counts per zone: ['main' => ['<external id>' => 2, ...], ...]
     * @param  Collection<string, array>  $cards  raw cards keyed by external id
     */
    public function export(array $zones, Collection $cards): string;

    /**
     * Parses a deck list and resolves its cards. Cards that can't be found are reported, not imported.
     */
    public function import(string $contents): ImportedDeck;
}
