<?php

namespace App\Collection;

use App\Contracts\CardProvider;
use App\Exceptions\CardSourceUnavailableException;
use App\Models\User;
use App\ViewModels\CardViewModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The card page's collection parts (logged-in players): the "In your collection" row
 * (resources/views/cards/partials/collection-row.blade.php) and a counter on each row of the Printings list.
 */
final class CardCollectionPanel
{
    /**
     * @param  array{owned: int, wanted: int, printings: array<string, int>}  $counts
     * @param  list<array{key: string, label: string, set_name: string, code: string, pack_code: string}>|null  $printings  null when they couldn't be loaded
     */
    private function __construct(private array $counts, private ?array $printings, private string $cardName) {}

    public static function for(?User $user, string $series, CardViewModel $card, CardProvider $provider): ?self
    {
        if (! $user) {
            return null;
        }

        try {
            $printings = $provider->printings($card->raw());
        } catch (CardSourceUnavailableException) {
            $printings = null;
        }

        return new self(app(CollectionService::class)->counts($user, $series, $card->collectionKey()), $printings, $card->name());
    }

    public function owned(): int
    {
        return $this->counts['owned'];
    }

    public function isOwned(): bool
    {
        return $this->owned() > 0;
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
     * The Printings list's rows with a counter each: every printing the source offers, plus any stored printing it no
     * longer offers (without a pack link). Null when the printings couldn't be loaded: the page then shows its usual
     * list without counters.
     *
     * @return Collection<int, array{set_name: string, code: string, url: ?string, printing: string, count: int, name: string}>|null
     */
    public function printRows(string $series): ?Collection
    {
        if ($this->printings === null) {
            return null;
        }

        $rows = collect($this->printings)->map(fn (array $printing) => [
            'set_name' => $printing['set_name'],
            'code' => $printing['code'],
            'url' => $printing['pack_code'] !== '' ? route('packs.show', [$series, $printing['pack_code'], Str::slug($printing['set_name'])]) : null,
            'printing' => $printing['key'],
            'count' => $this->counts['printings'][$printing['key']] ?? 0,
            'name' => "{$this->cardName} ({$printing['label']})",
        ]);
        $offered = $rows->pluck('printing')->all();

        foreach (collect($this->counts['printings'])->except(['', ...$offered]) as $key => $count) {
            $rows->push(['set_name' => (string) $key, 'code' => (string) $key, 'url' => null, 'printing' => (string) $key, 'count' => $count, 'name' => "{$this->cardName} ($key)"]);
        }

        return $rows->values();
    }
}
