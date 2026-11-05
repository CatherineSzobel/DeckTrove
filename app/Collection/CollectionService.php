<?php

namespace App\Collection;

use App\Cards\CardCache;
use App\Models\CollectionItem;
use App\Models\Deck;
use App\Models\User;
use App\Services\CardService;
use App\Services\DeckService;
use App\Services\PackService;
use App\ViewModels\CardViewModel;
use App\ViewModels\PackViewModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Every collection and wishlist rule (spec: .superpowers/rework/specs/2026-10-08-collection-tracker-design.md).
 * Rows are per card key (all printings of a card share one) and printing ('' = unspecified); wishlist rows are always
 * unspecified. Adding owned copies takes as many off the wishlist; nothing ever adds back to it.
 */
class CollectionService
{
    public const MAX = 999;

    public function __construct(
        private readonly CardService $cards,
        private readonly DeckService $decks,
        private readonly PackService $packs,
    ) {}

    public function add(User $user, string $game, string $cardId, string $list = CollectionItem::OWNED, string $printing = '', int $amount = 1): void
    {
        $card = $this->card($game, $cardId);

        if ($list === CollectionItem::OWNED && $printing !== '' && ! in_array($printing, array_column($this->cards->for($game)->printings($card->raw()), 'key'), true)) {
            throw ValidationException::withMessages(['card' => 'That card has no such printing.']);
        }

        $this->addCard($user, $game, $card, $cardId, $list, $printing, $amount);
    }

    /** add() for a card that is already loaded, so bulk actions don't look every card up again. */
    private function addCard(User $user, string $game, CardViewModel $card, string $cardId, string $list, string $printing, int $amount): void
    {
        $printing = $list === CollectionItem::WANTED ? '' : $printing;

        DB::transaction(function () use ($user, $game, $card, $cardId, $list, $printing, $amount) {
            $this->increment($user, $game, $card, $cardId, $list, $printing, $amount);

            if ($list === CollectionItem::OWNED) {
                $this->decrement($this->row($user, $game, $card->collectionKey(), CollectionItem::WANTED, ''), $amount);
            }
        }, 3); // Retried: two quick adds of a new card can deadlock on MySQL's gap locks.
    }

    public function remove(User $user, string $game, string $cardId, string $list = CollectionItem::OWNED, string $printing = ''): void
    {
        // The key comes from the user's own rows, so a card the source dropped (or a Scryfall outage) can still be removed.
        $key = $this->keyFor($user, $game, $cardId);
        $printing = $list === CollectionItem::WANTED ? '' : $printing;

        DB::transaction(function () use ($user, $game, $key, $list, $printing) {
            $row = $this->row($user, $game, $key, $list, $printing);

            if (! $row && $list === CollectionItem::OWNED && $printing === '' && $this->rows($user, $game, $key)->owned()->exists()) {
                throw ValidationException::withMessages(['card' => 'Choose which printing to remove.']);
            }

            if (! $row) {
                throw ValidationException::withMessages(['card' => 'There is no copy of that card to remove.']);
            }

            $this->decrement($row, 1);
        });
    }

    /** The card key of a card the user holds (on either list), looked up by its external id, or null. */
    private function storedKey(User $user, string $game, string $cardId): ?string
    {
        return $user->collectionItems()->forGame($game)
            ->whereHas('card', fn ($query) => $query->where('game', $game)->where('external_id', $cardId))
            ->value('card_key');
    }

    /**
     * The card key for an external id: from the user's rows when they hold that very printing, else from the card
     * itself (a Magic row keeps the printing that created it, so another printing of the card isn't in the rows).
     */
    private function keyFor(User $user, string $game, string $cardId): string
    {
        return $this->storedKey($user, $game, $cardId) ?? $this->card($game, $cardId)->collectionKey();
    }

    /**
     * Owned copies of many cards at once (a page of tiles), in one query: total copies, and whether any are
     * unspecified (the tile's "−" only takes those). Cards with no copies are left out.
     *
     * @param  list<string>  $cardKeys
     * @return array<string, array{count: int, removable: bool}>
     */
    public function ownedCounts(User $user, string $game, array $cardKeys): array
    {
        if ($cardKeys === []) {
            return [];
        }

        return $user->collectionItems()->owned()->forGame($game)
            ->whereIn('card_key', array_values(array_unique($cardKeys)))
            ->selectRaw('card_key, SUM(quantity) AS copies, '.CollectionItem::UNSPECIFIED_SUM.' AS unspecified')
            ->groupBy('card_key')
            ->get()
            ->mapWithKeys(fn ($row) => [(string) $row->card_key => ['count' => (int) $row->copies, 'removable' => (int) $row->unspecified > 0]])
            ->all();
    }

    /**
     * What a counter shows after a change, from the card's counts(): the list's copies of the card (owned total,
     * wanted, or one printing's owned copies) and whether its "−" has a copy to take.
     *
     * @param  array{owned: int, wanted: int, printings: array<string, int>}  $counts
     * @return array{count: int, removable: bool}
     */
    public static function counterState(array $counts, string $list = CollectionItem::OWNED, string $printing = ''): array
    {
        if ($list === CollectionItem::WANTED) {
            return ['count' => $counts['wanted'], 'removable' => $counts['wanted'] > 0];
        }

        if ($printing !== '') {
            $copies = $counts['printings'][$printing] ?? 0;

            return ['count' => $copies, 'removable' => $copies > 0];
        }

        return ['count' => $counts['owned'], 'removable' => self::removable($counts)];
    }

    /**
     * Whether a card-level owned "−" has a copy to take: it only takes unspecified copies (printing copies come off
     * in the card page's Printings list).
     *
     * @param  array{owned: int, wanted: int, printings: array<string, int>}  $counts
     */
    public static function removable(array $counts): bool
    {
        return ($counts['printings'][''] ?? 0) > 0;
    }

    /**
     * The card's owned and wanted totals, so a page's other counters for the same card can follow a change.
     *
     * @return array{owned: int, wanted: int, printings: array<string, int>}
     */
    public function cardCounts(User $user, string $game, string $cardId): array
    {
        return $this->counts($user, $game, $this->keyFor($user, $game, $cardId));
    }

    /**
     * The card page's heart: on puts the card on the wishlist (one copy, unless it is already there); off takes it
     * off the wishlist entirely, however many copies were wanted.
     */
    public function setWanted(User $user, string $game, string $cardId, bool $want): void
    {
        if (! $want) {
            $this->rows($user, $game, $this->keyFor($user, $game, $cardId))->wanted()->delete();

            return;
        }

        $card = $this->card($game, $cardId);

        if ($this->rows($user, $game, $card->collectionKey())->wanted()->exists()) {
            return;
        }

        $this->addCard($user, $game, $card, $cardId, CollectionItem::WANTED, '', 1);
    }

    /**
     * @return array{owned: int, wanted: int, printings: array<string, int>}
     */
    public function counts(User $user, string $game, string $cardKey): array
    {
        $rows = $this->rows($user, $game, $cardKey)->get();
        $owned = $rows->where('list', CollectionItem::OWNED);

        return [
            'owned' => (int) $owned->sum('quantity'),
            'wanted' => (int) $rows->where('list', CollectionItem::WANTED)->sum('quantity'),
            'printings' => $owned->sortBy('printing')->mapWithKeys(fn (CollectionItem $item) => [$item->printing => $item->quantity])->all(),
        ];
    }

    public function deckNeeds(User $user, Deck $deck): DeckNeeds
    {
        $lines = $this->deckLines($deck);
        $owned = $this->ownedByKey($user, $deck->game, array_keys($lines));

        $ownedTotal = 0;
        $missing = [];
        $cost = null;
        $unpriced = 0;

        foreach ($lines as $key => $line) {
            $own = min($owned[$key] ?? 0, $line['need']);
            $ownedTotal += $own;

            if ($own < $line['need']) {
                $short = $line['need'] - $own;
                $missing[] = ['external_id' => $line['external_id'], 'name' => $line['name'], 'need' => $line['need'], 'own' => $own];

                if ($line['price'] === null) {
                    $unpriced += $short;
                } else {
                    $cost = ($cost ?? 0.0) + $short * $line['price'];
                }
            }
        }

        return new DeckNeeds($ownedTotal, array_sum(array_column($lines, 'need')), $missing, $cost === null ? null : round($cost, 2), $unpriced);
    }

    /** Owns the copies the deck is short of; returns how many were added. */
    public function topUpDeck(User $user, Deck $deck): int
    {
        return $this->fillFromDeck($user, $deck, CollectionItem::OWNED, fn (array $line) => $line['need'] - $line['own']);
    }

    /** Wishes for the copies the deck is short of, minus what is already wished for; returns how many were added. */
    public function wishMissing(User $user, Deck $deck): int
    {
        $wanted = $this->wantedByKey($user, $deck->game);

        return $this->fillFromDeck($user, $deck, CollectionItem::WANTED,
            fn (array $line, string $key) => $line['need'] - $line['own'] - ($wanted[$key] ?? 0));
    }

    /** Adds one copy of every card in the set, as that set's printing; returns how many were added. */
    public function addSet(User $user, string $series, string $setCode, ?string $slug): int
    {
        $config = config("series.$series");
        $provider = $this->packs->for($series);
        $setName = (new PackViewModel($provider->find($setCode, $slug), $config['pack'] ?? []))->name();
        // Fetched before the transaction, so no row stays locked while the source is paged.
        $cards = $provider->cards($setCode, $slug);

        return DB::transaction(function () use ($user, $series, $setCode, $config, $cards, $setName) {
            $added = 0;

            foreach ($cards as $raw) {
                $card = new CardViewModel((array) $raw, $config);
                $printing = $card->printingIn($setCode, $setName);
                $cardId = $card->id();

                // A set entry without an id can't be stored.
                if ($cardId === null || ($this->counts($user, $series, $card->collectionKey())['printings'][$printing] ?? 0) >= self::MAX) {
                    continue;
                }

                $this->addCard($user, $series, $card, $cardId, CollectionItem::OWNED, $printing, 1);
                $added++;
            }

            return $added;
        });
    }

    /** @return array{total: float, unpriced: int} */
    public function wishlistCost(User $user): array
    {
        $total = 0.0;
        $unpriced = 0;

        foreach ($user->collectionItems()->wanted()->with('card')->get()->groupBy('game') as $game => $items) {
            $raw = $this->cards->for($game)->findMany($items->map(fn ($item) => $item->card->external_id)->all());

            foreach ($items as $item) {
                $card = $raw->get($item->card->external_id);
                $price = $card ? (new CardViewModel($card, config("series.$game")))->priceValue() : null;

                $price === null ? $unpriced += $item->quantity : $total += $price * $item->quantity;
            }
        }

        return ['total' => round($total, 2), 'unpriced' => $unpriced];
    }

    /**
     * The deck's needs per card key: [key => ['external_id', 'name', 'need', 'price']], in deck order. Printings of
     * one card share a key, so they add up. Cards the source dropped count by their external id under their stored name.
     *
     * @return array<string, array{external_id: string, name: string, need: int, price: ?float, card: CardViewModel, known: bool}>
     */
    private function deckLines(Deck $deck): array
    {
        $config = config("series.{$deck->game}");
        $copies = $this->decks->totalCopies($this->decks->zoneCounts($deck));

        $raw = $this->cards->for($deck->game)->findMany(array_keys($copies));
        $names = $deck->cards()->pluck('name', 'external_id');
        $lines = [];

        foreach ($copies as $id => $count) {
            $card = new CardViewModel($raw->get($id) ?? ['id' => $id, 'name' => $names[$id] ?? 'Unknown Card'], $config);
            $key = $card->collectionKey();

            $lines[$key] ??= ['external_id' => (string) $id, 'name' => $card->name(), 'need' => 0, 'price' => $card->priceValue(), 'card' => $card, 'known' => $raw->has($id)];
            $lines[$key]['need'] += $count;
        }

        return $lines;
    }

    /** @return array<string, int> */
    private function ownedByKey(User $user, string $game, array $keys): array
    {
        return array_map(fn (array $counts) => $counts['count'], $this->ownedCounts($user, $game, $keys));
    }

    /** @return array<string, int> */
    private function wantedByKey(User $user, string $game): array
    {
        return $user->collectionItems()->wanted()->forGame($game)->pluck('quantity', 'card_key')->all();
    }

    /**
     * Adds $amount($line, $key) copies of each card the deck needs to $list, all or nothing; returns how many went in.
     * Cards the source no longer has are skipped.
     */
    private function fillFromDeck(User $user, Deck $deck, string $list, callable $amount): int
    {
        $lines = $this->deckLines($deck);
        $owned = $this->ownedByKey($user, $deck->game, array_keys($lines));

        return DB::transaction(function () use ($user, $deck, $list, $amount, $lines, $owned) {
            $added = 0;

            foreach ($lines as $key => $line) {
                $copies = $line['known'] ? $amount([...$line, 'own' => min($owned[$key] ?? 0, $line['need'])], (string) $key) : 0;

                if ($copies > 0) {
                    $this->addCard($user, $deck->game, $line['card'], $line['external_id'], $list, '', $copies);
                    $added += $copies;
                }
            }

            return $added;
        });
    }

    /** The card as a view model, or a validation error when the series doesn't know it. */
    private function card(string $game, string $cardId): CardViewModel
    {
        try {
            $raw = $this->cards->for($game)->find($cardId);
        } catch (NotFoundHttpException) {
            throw ValidationException::withMessages(['card' => "The card $cardId could not be found."]);
        }

        return new CardViewModel($raw, config("series.$game"));
    }

    private function increment(User $user, string $game, CardViewModel $card, string $cardId, string $list, string $printing, int $amount): void
    {
        $row = $this->row($user, $game, $card->collectionKey(), $list, $printing);
        $this->guardMax($row, $amount);

        if ($row) {
            $row->increment('quantity', $amount);

            return;
        }

        try {
            $user->collectionItems()->create([
                'list' => $list,
                'game' => $game,
                'card_key' => $card->collectionKey(),
                'card_id' => CardCache::store($game, collect([$cardId => $card->raw()]))[$cardId],
                'printing' => $printing,
                'quantity' => $amount,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // A double "+" created the row first: add to that one instead.
            $row = $this->row($user, $game, $card->collectionKey(), $list, $printing) ?? throw $e;
            $this->guardMax($row, $amount);
            $row->increment('quantity', $amount);
        }
    }

    private function guardMax(?CollectionItem $row, int $amount): void
    {
        if (($row?->quantity ?? 0) + $amount > self::MAX) {
            throw ValidationException::withMessages(['card' => 'You can keep at most '.self::MAX.' copies of a card.']);
        }
    }

    private function decrement(?CollectionItem $row, int $amount): void
    {
        if (! $row) {
            return;
        }

        $row->quantity > $amount ? $row->decrement('quantity', $amount) : $row->delete();
    }

    private function row(User $user, string $game, string $key, string $list, string $printing): ?CollectionItem
    {
        return $this->rows($user, $game, $key)->where('list', $list)->where('printing', $printing)->lockForUpdate()->first();
    }

    private function rows(User $user, string $game, string $key): HasMany
    {
        return $user->collectionItems()->forGame($game)->where('card_key', $key);
    }
}
