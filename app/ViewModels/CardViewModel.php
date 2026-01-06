<?php

namespace App\ViewModels;

use Illuminate\Support\Arr;

class CardViewModel
{
    public function __construct(protected array $card, protected array $config) {}

    public function id(): ?string
    {
        return $this->card['id'] ?? null;
    }

    public function name(): string
    {
        return $this->card['name'] ?? 'Unknown Card';
    }

    public function image(): string
    {
        return ($this->config['image'] ?? fn() => '')($this->card);
    }

    public function link(): string
    {
        $prefix = $this->config['link_prefix'] ?? '/';
        return $prefix . '/card/' . ($this->card['id'] ?? '');
    }

    public function type(): string
    {
        $key = $this->isMultiFace() ? 'colorless_type' : 'type_field';
        return ($this->config[$key] ?? fn() => '')($this->card);
    }

    public function subtype(): string
    {
        return Arr::get($this->card, $this->config['subtype_field'] ?? '', '-');
    }

    public function description(): ?string
    {
        $key = $this->isMultiFace()
            ? 'colorless_description'
            : 'description';

        return ($this->config[$key] ?? fn() => null)($this->card);
    }

    public function stats(): array
    {
        $resolver = $this->config['stats'] ?? null;

        return is_callable($resolver)
            ? $resolver($this->card)
            : [];
    }

    public function hasStats(): bool
    {
        return collect($this->stats())
            ->pluck('value')
            ->filter(fn($v) => $v !== null)
            ->isNotEmpty();
    }

    public function statLine(): string
    {
        return collect($this->stats())
            ->map(fn($stat) => "{$stat['label']}: " . ($stat['value'] ?? '-'))
            ->implode(' / ');
    }
    public function showStats(): string
    {
        return collect($this->stats())
            ->map(fn($stat) => ($stat['value'] ?? '-'))
            ->implode(' / ');
    }

    public function statsLabels(): array
    {
        return collect($this->stats())
            ->pluck('label')
            ->toArray();
    }

    public function setName(): string
    {
        return Arr::get($this->card, $this->config['set_field'] ?? '') ?? 'No Set';
    }

    public function rarity(): ?string
    {
        return $this->rarityRaw();
    }

    protected function rarityRaw(): string
    {
        return Arr::get($this->card, $this->config['rarity_field'] ?? '') ?? 'unknown';
    }
    public function rarityLabel(): string
    {
        return ucfirst($this->rarityRaw());
    }

    public function rarityColor(): string
    {
        return $this->config['rarity_colors'][$this->rarityRaw()] ?? 'text-gray-500';
    }

    public function price(): string
    {
        return Arr::get($this->card, $this->config['price_field'] ?? 'N/A') ?: 'N/A';
    }

    public function isMultiFace(): bool
    {
        return isset($this->card['card_faces']) && is_array($this->card['card_faces']) && count($this->card['card_faces']) > 0;
    }

    public function printSets(): array
    {
        return ($this->config['print_sets'] ?? fn() => [])($this->card);
    }
}
