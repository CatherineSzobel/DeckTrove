<?php

namespace App\ViewModels;

use App\Cards\CardFace;
use App\Cards\CardMapper;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Presents a raw card from any series, using that series' mapper and config from config/series.php.
 */
class CardViewModel
{
    protected CardMapper $mapper;

    /** @var list<CardFace>|null */
    private ?array $faces = null;

    public function __construct(protected array $card, protected array $config)
    {
        $this->mapper = app($config['mapper']);
    }

    /** The raw card this presents, e.g. to store its name and image locally. */
    public function raw(): array
    {
        return $this->card;
    }

    public function id(): ?string
    {
        return isset($this->card['id']) ? (string) $this->card['id'] : null;
    }

    public function name(): string
    {
        return $this->card['name'] ?? 'Unknown Card';
    }

    /**
     * The name in lower case, for filtering on the pack page.
     */
    public function searchName(): string
    {
        return mb_strtolower($this->name());
    }

    public function image(): string
    {
        return $this->mapper->image($this->card);
    }

    public function imageSmall(): string
    {
        return $this->mapper->imageSmall($this->card);
    }

    /**
     * Images of every face for double-faced cards; empty for regular cards.
     */
    public function faceImages(): array
    {
        return array_map(fn (CardFace $face) => $face->image(), $this->faces());
    }

    /**
     * Every face of a double-faced card, each with its own image, name, type, text and stats; empty for regular cards.
     *
     * @return list<CardFace>
     */
    public function faces(): array
    {
        return $this->faces ??= $this->mapper->faces($this->card);
    }

    /**
     * Whether the card has more than one face to flip between.
     */
    public function hasFaces(): bool
    {
        return count($this->faceImages()) > 1;
    }

    public function link(): string
    {
        return url(($this->config['link_prefix'] ?? '').'/card/'.$this->id());
    }

    public function type(): string
    {
        return $this->mapper->type($this->card);
    }

    public function subtype(): string
    {
        return $this->mapper->subtype($this->card);
    }

    /**
     * Type and subtype together, which is what deck rules match against (e.g. "XYZ Monster", "Basic Land").
     */
    public function fullType(): string
    {
        return trim($this->type().' '.$this->subtype());
    }

    public function typeLine(): string
    {
        return $this->mapper->typeLine($this->card);
    }

    public function description(): string
    {
        return $this->mapper->description($this->card);
    }

    public function stats(): array
    {
        return $this->mapper->stats($this->card);
    }

    public function hasStats(): bool
    {
        return collect($this->stats())->pluck('value')->contains(fn ($v) => $v !== null);
    }

    public function statLine(): string
    {
        return collect($this->stats())
            ->map(fn ($stat) => "{$stat['label']}: ".($stat['value'] ?? '-'))
            ->implode(' / ');
    }

    /**
     * The boxes on the card page: the mana cost (Magic), the stats that have a value, then the price.
     *
     * @return list<array{label: string, value: string}>
     */
    public function detailStats(): array
    {
        $manaCost = $this->mapper->manaCost($this->card);

        return collect($manaCost === null ? [] : [['label' => 'Mana cost', 'value' => $manaCost]])
            ->merge(collect($this->stats())
                ->reject(fn (array $stat) => $stat['value'] === null)
                ->map(fn (array $stat) => ['label' => $stat['label'], 'value' => (string) $stat['value']])
                ->values())
            ->push(['label' => 'Price', 'value' => $this->formattedPrice()])
            ->values()
            ->all();
    }

    public function setName(): string
    {
        return $this->mapper->setName($this->card) ?? 'No Set';
    }

    public function rarity(): string
    {
        return $this->mapper->rarity($this->card) ?? 'unknown';
    }

    public function rarityLabel(): string
    {
        return ucfirst($this->rarity());
    }

    /**
     * Whether the card page gives this card the rainbow foil sheen (the series' `holo_rarities`).
     */
    public function isHolo(): bool
    {
        return $this->mapper->isHolo($this->card, $this->config['holo_rarities'] ?? []);
    }

    public function priceValue(): ?float
    {
        return $this->mapper->priceValue($this->card);
    }

    /**
     * The price with the currency symbol, e.g. "€0.10", or "N/A".
     */
    public function formattedPrice(): string
    {
        $value = $this->priceValue();

        return $value === null ? 'N/A' : Money::format($value);
    }

    /**
     * The deck stats groups this card falls into, e.g. ['category' => 'Monster', 'level' => 'Level 4'].
     *
     * @return array<string, string>
     */
    public function statFacets(): array
    {
        return $this->mapper->statFacets($this->card);
    }

    public function collectionKey(): string
    {
        return $this->mapper->collectionKey($this->card);
    }

    public function printingIn(string $setCode, ?string $setName = null): string
    {
        return $this->mapper->printingIn($this->card, $setCode, $setName);
    }

    /**
     * The card page's Printings list, the same rows for guests and players: each printing from the card source
     * (CardProvider::printings), linked to its pack page, with the player's copies of it ($counts, printing key =>
     * copies). Printings a player still owns that the source no longer offers come last, without a link.
     *
     * @param  list<array{key: string, label: string, set_name: string, code: string, pack_code: string}>  $printings
     * @param  array<string, int>  $counts
     * @return Collection<int, array{set_name: string, code: string, url: ?string, printing: string, count: int, name: string}>
     */
    public function printRows(string $series, array $printings, array $counts = []): Collection
    {
        $rows = collect($printings)->map(fn (array $printing) => [
            'set_name' => $printing['set_name'],
            'code' => $printing['code'],
            'url' => $printing['pack_code'] !== '' ? route('packs.show', [$series, $printing['pack_code'], Str::slug($printing['set_name'])]) : null,
            'printing' => $printing['key'],
            'count' => $counts[$printing['key']] ?? 0,
            'name' => "{$this->name()} ({$printing['label']})",
        ]);
        $offered = $rows->pluck('printing')->all();

        foreach (collect($counts)->except(['', ...$offered]) as $key => $count) {
            $rows->push(['set_name' => (string) $key, 'code' => (string) $key, 'url' => null, 'printing' => (string) $key, 'count' => $count, 'name' => "{$this->name()} ($key)"]);
        }

        return $rows->values();
    }

    /**
     * The most copies allowed in a format, or null when the format adds no limit.
     */
    public function copyLimit(string $format): ?int
    {
        return $this->mapper->copyLimit($this->card, $format);
    }

    /**
     * Limits in the series' formats that limit this card, e.g. ['tcg' => 1, 'ocg' => 2].
     *
     * @return array<string, int>
     */
    public function copyLimits(): array
    {
        return collect(array_keys($this->config['deck']['formats'] ?? []))
            ->mapWithKeys(fn (string $format) => [$format => $this->copyLimit($format)])
            ->reject(fn (?int $limit) => $limit === null)
            ->all();
    }

    /**
     * Everything the deck builder (resources/js/deck-builder.js) needs about a card, as one JSON-ready array.
     */
    public function builderCard(): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'image' => $this->image(),
            'imageSmall' => $this->imageSmall(),
            'type' => $this->fullType(),
            'description' => $this->description(),
            'stats' => $this->hasStats() ? $this->statLine() : null,
            'price' => $this->priceValue(),
            'limits' => $this->copyLimits(),
        ];
    }
}
