<?php

namespace App\Packs;

use App\ViewModels\CardViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The name / rarity filter on a pack page (?name=&rarity=). The server applies it so the page works without JS;
 * resources/js/pack-page.js applies the same rules live (resources/js/packs/filter.js).
 */
final class PackCardFilter
{
    private const NAME_LENGTH = 100;

    private function __construct(private ?string $name, private ?string $rarity) {}

    public static function fromRequest(Request $request): self
    {
        $name = mb_substr(trim((string) self::string($request, 'name')), 0, self::NAME_LENGTH);
        $rarity = trim((string) self::string($request, 'rarity'));

        return new self($name === '' ? null : $name, $rarity === '' ? null : $rarity);
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function rarity(): ?string
    {
        return $this->rarity;
    }

    public function isFiltering(): bool
    {
        return $this->name !== null || $this->rarity !== null;
    }

    public function isRarity(int|string $value): bool
    {
        return $this->rarity === (string) $value;
    }

    public function matches(CardViewModel $card): bool
    {
        return ($this->name === null || mb_stripos($card->name(), $this->name) !== false)
            && ($this->rarity === null || $card->rarity() === $this->rarity);
    }

    public function count(Collection $cards): int
    {
        return $cards->filter(fn (CardViewModel $card) => $this->matches($card))->count();
    }

    /**
     * "126 cards", or "12 of 126 cards" while filtering (same wording as countLabel() in resources/js/packs/filter.js).
     */
    public function countLabel(int $shown, int $total): string
    {
        $cards = number_format($total).' '.Str::plural('card', $total);

        return $this->isFiltering() ? number_format($shown).' of '.$cards : $cards;
    }

    private static function string(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : null;
    }
}
