<?php

namespace App\ViewModels;

use App\Cards\CardMapper;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Presents a raw card from any series, using that series' mapper and config from config/series.php.
 */
class CardViewModel
{
    protected CardMapper $mapper;

    public function __construct(protected array $card, protected array $config)
    {
        $this->mapper = app($config['mapper']);
    }

    public function id(): ?string
    {
        return isset($this->card['id']) ? (string) $this->card['id'] : null;
    }

    public function name(): string
    {
        return $this->card['name'] ?? 'Unknown Card';
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
        return $this->mapper->faceImages($this->card);
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

        return $value === null ? 'N/A' : config('app.currency.symbol').number_format($value, 2);
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

    public function printSets(): array
    {
        return $this->mapper->printSets($this->card);
    }

    /**
     * The sets this card was printed in, once each, with a link to the pack page. A print code looks like
     * "LOB-EN001"; the pack code is the part before the dash, and the set name's slug picks between products that
     * share a pack code.
     *
     * @return Collection<int, array{set_name: string, code: string, url: string}>
     */
    public function printPacks(string $series): Collection
    {
        return collect($this->printSets())
            ->map(fn (array $set) => ['code' => explode('-', $set['set_code'] ?? '')[0], 'set_name' => $set['set_name'] ?? 'Unknown'])
            ->filter(fn (array $set) => $set['code'] !== '')
            ->unique('set_name')
            ->map(fn (array $set) => [
                'set_name' => $set['set_name'],
                'code' => $set['code'],
                'url' => route('packs.show', [$series, $set['code'], Str::slug($set['set_name'])]),
            ])
            ->values();
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
