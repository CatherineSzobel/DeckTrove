<?php

namespace App\ViewModels;

use Illuminate\Support\Arr;

/**
 * Presents a raw card from any series through the field mapping in config/series.php.
 */
class CardViewModel
{
    public function __construct(protected array $card, protected array $config) {}

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
        return $this->resolve('image', '');
    }

    public function imageSmall(): string
    {
        return $this->resolve('image_small') ?: $this->image();
    }

    /**
     * Images of every face for double-faced cards; empty for regular cards.
     */
    public function faceImages(): array
    {
        return $this->resolve('face_images', []);
    }

    public function link(): string
    {
        return url(($this->config['link_prefix'] ?? '').'/card/'.$this->id());
    }

    public function type(): string
    {
        return (string) $this->resolve('type_field', '');
    }

    public function subtype(): string
    {
        return (string) Arr::get($this->card, $this->config['subtype_field'] ?? '', '');
    }

    public function description(): string
    {
        return (string) $this->resolve('description', '');
    }

    /**
     * @return array{left: array{label: string, value: mixed}, right: array{label: string, value: mixed}}|array{}
     */
    public function stats(): array
    {
        return $this->resolve('stats', []);
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
        return Arr::get($this->card, $this->config['set_field'] ?? '') ?? 'No Set';
    }

    public function rarity(): string
    {
        return Arr::get($this->card, $this->config['rarity_field'] ?? '') ?? 'unknown';
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
        return Arr::get($this->card, $this->config['price_field'] ?? '') ?: 'N/A';
    }

    public function printSets(): array
    {
        return $this->resolve('print_sets', []);
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
            'data-card-type' => trim($this->type().' '.$this->subtype()),
            'data-card-desc' => $this->description(),
        ];
    }

    private function resolve(string $key, mixed $default = null): mixed
    {
        $resolver = $this->config[$key] ?? null;

        return is_callable($resolver) ? $resolver($this->card) : $default;
    }
}
