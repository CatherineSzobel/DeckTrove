<?php

namespace App\ViewModels;

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
        return $this->code() ? url("{$this->config['link_prefix']}/pack/{$this->code()}") : '#';
    }
}
