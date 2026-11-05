<?php

namespace App\Collection;

use App\Models\User;
use App\ViewModels\CardViewModel;

/**
 * The owned counts under a page of card tiles (card database, pack page), loaded in one query. Guests get none.
 */
final class TileCounts
{
    /** @param array<string, array{count: int, removable: bool}> $counts */
    private function __construct(private readonly bool $shows, private readonly array $counts) {}

    /** @param iterable<CardViewModel> $cards */
    public static function for(?User $user, string $series, iterable $cards): self
    {
        if (! $user) {
            return new self(false, []);
        }

        $keys = collect($cards)->map(fn (CardViewModel $card) => $card->collectionKey())->values()->all();

        return new self(true, app(CollectionService::class)->ownedCounts($user, $series, $keys));
    }

    /** Whether tiles show a counter (logged-in players only). */
    public function shows(): bool
    {
        return $this->shows;
    }

    public function count(CardViewModel $card): int
    {
        return $this->counts[$card->collectionKey()]['count'] ?? 0;
    }

    /** Whether the tile's "−" has an unspecified copy to take. */
    public function removable(CardViewModel $card): bool
    {
        return $this->counts[$card->collectionKey()]['removable'] ?? false;
    }
}
