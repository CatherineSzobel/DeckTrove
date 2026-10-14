<?php

namespace App\ViewModels;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Arr;

class PackViewModel
{
    public function __construct(
        protected array $pack,
        protected array $config = []
    ) {}

    public function name(): string
    {
        return Arr::get(
            $this->pack,
            $this->config['name'] ?? '',
            'Unknown Pack'
        );
    }

    public function image(): ?string
    {
        return Arr::get($this->pack, $this->config['image'] ?? '');
    }

    public function code(): ?string
    {
        return Arr::get($this->pack, $this->config['code'] ?? '');
    }

    public function release(): ?string
    {
        return Arr::get($this->pack, $this->config['release_date'] ?? '');
    }

    public function cardCount(): ?int
    {
        return Arr::get($this->pack, $this->config['card_count'] ?? '');
    }

    public function type(): ?string
    {
        if (! isset($this->config['type'])) {
            return null;
        }

        return Arr::get($this->pack, $this->config['type']);
    }

    public function link(): string
    {
        if (! $this->code()) {
            return '#';
        }

        $slug = Arr::get($this->pack, $this->config['slug'] ?? '');

        return url("{$this->config['link_prefix']}/pack/{$this->code()}".($slug ? "/$slug" : ''));
    }

    public function codeLabel(): ?string
    {
        return $this->code() === null ? null : strtoupper($this->code());
    }

    /**
     * The release date like "Mar 8, 2002", or null when it's missing or not a date.
     */
    public function releaseLabel(): ?string
    {
        $release = $this->release();

        if (! $release) {
            return null;
        }

        try {
            return Carbon::parse($release)->format('M j, Y');
        } catch (InvalidFormatException) {
            return null;
        }
    }

    /**
     * Magic's set type as words, e.g. "Draft innovation"; null for series without types.
     */
    public function typeLabel(): ?string
    {
        $type = $this->type();

        return $type ? ucfirst(str_replace('_', ' ', $type)) : null;
    }

    /**
     * The stat boxes on the pack page, without the empty ones.
     *
     * @return list<array{label: string, value: string}>
     */
    public function facts(): array
    {
        return collect([
            'Code' => $this->codeLabel(),
            'Type' => $this->typeLabel(),
            'Cards' => $this->cardCount() === null ? null : number_format($this->cardCount()),
            'Released' => $this->releaseLabel(),
        ])
            ->reject(fn (?string $value) => $value === null || $value === '')
            ->map(fn (string $value, string $label) => ['label' => $label, 'value' => $value])
            ->values()
            ->all();
    }

    /**
     * "LOB · Released Mar 8, 2002" on the pack list, leaving out what's missing.
     */
    public function metaLine(): string
    {
        return collect([$this->codeLabel(), $this->releaseLabel() ? 'Released '.$this->releaseLabel() : null])
            ->filter()
            ->implode(' · ');
    }

    /**
     * How x-pack.cover draws the pack: its art, its set symbol on a tile (config `pack.cover`), or a code tile.
     */
    public function coverKind(): string
    {
        if (! $this->image()) {
            return 'code';
        }

        return ($this->config['cover'] ?? 'art') === 'symbol' ? 'symbol' : 'art';
    }

    /**
     * The tile colour (0–5) for packs drawn as tiles; the same code always gets the same colour.
     */
    public function tone(): int
    {
        return crc32((string) $this->code()) % 6;
    }
}
