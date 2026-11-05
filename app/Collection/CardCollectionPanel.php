<?php

namespace App\Collection;

use App\Models\User;
use App\ViewModels\CardViewModel;

/**
 * The card page's collection parts (logged-in players): the "In your collection" row
 * (resources/views/cards/partials/collection-row.blade.php) and a counter on each row of the Printings list.
 */
final class CardCollectionPanel
{
    /**
     * @param  array{owned: int, wanted: int, printings: array<string, int>}  $counts
     */
    private function __construct(private array $counts) {}

    public static function for(?User $user, string $series, CardViewModel $card): ?self
    {
        return $user ? new self(app(CollectionService::class)->counts($user, $series, $card->collectionKey())) : null;
    }

    public function owned(): int
    {
        return $this->counts['owned'];
    }

    /** Whether the row's "−" has an unspecified copy to take (printing copies come off in the Printings list). */
    public function removable(): bool
    {
        return CollectionService::removable($this->counts);
    }

    /** "Not in your collection yet", "Any printing", or "2 by printing · 1 any printing". */
    public function note(): string
    {
        return self::noteFor($this->counts);
    }

    /**
     * The row's note for a card's counts (also sent with a change, so the row can follow its Printings counters).
     *
     * @param  array{owned: int, wanted: int, printings: array<string, int>}  $counts
     */
    public static function noteFor(array $counts): string
    {
        if ($counts['owned'] < 1) {
            return 'Not in your collection yet';
        }

        $byPrinting = (int) collect($counts['printings'])->except('')->sum();
        $any = $counts['printings'][''] ?? 0;

        if ($byPrinting === 0) {
            return 'Any printing';
        }

        return $any > 0 ? "$byPrinting by printing · $any any printing" : "$byPrinting by printing";
    }

    public function wanted(): bool
    {
        return $this->counts['wanted'] > 0;
    }

    /** For collectionHeart's start(): the server-rendered state as a data attribute. */
    public function wantedFlag(): string
    {
        return $this->wanted() ? '1' : '0';
    }

    /** The heart's aria-pressed as rendered by the server. */
    public function pressed(): string
    {
        return $this->wanted() ? 'true' : 'false';
    }

    public function heartLabel(): string
    {
        return $this->wanted() ? 'Wanted' : 'Want it';
    }

    /** What the heart's form posts: pressing it flips the wish. */
    public function wantValue(): string
    {
        return $this->wanted() ? '0' : '1';
    }

    /**
     * Copies per printing key, for the Printings list's counters (CardViewModel::printRows).
     *
     * @return array<string, int>
     */
    public function printingCounts(): array
    {
        return $this->counts['printings'];
    }
}
