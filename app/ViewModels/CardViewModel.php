<?php

namespace App\ViewModels;

use App\Cards\CardMapper;

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

    public function rarityColor(): string
    {
        return $this->config['rarity_colors'][strtolower($this->rarity())] ?? 'text-gray-500';
    }

    public function price(): string
    {
        return $this->mapper->price($this->card) ?: 'N/A';
    }

    public function printSets(): array
    {
        return $this->mapper->printSets($this->card);
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
     * The attributes the deck builder JS reads from a card element.
     */
    public function deckBuilderData(): array
    {
        return [
            'data-card-id' => $this->id(),
            'data-card-name' => $this->name(),
            'data-card-image' => $this->image(),
            'data-card-type' => $this->fullType(),
            'data-card-desc' => $this->description(),
        ];
    }
}
