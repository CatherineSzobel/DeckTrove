<?php

namespace App\Cards;

use Illuminate\Support\Facades\Storage;

/**
 * Yu-Gi-Oh! cards in the YGOPRODeck format.
 */
class YugiohCardMapper extends CardMapper
{
    /** Banlist statuses in YGOPRODeck's `banlist_info`, as copies allowed. */
    private const BANLIST_LIMITS = ['Forbidden' => 0, 'Limited' => 1, 'Semi-Limited' => 2];

    public function image(array $card): string
    {
        return $this->hostedImage($card, 'cards')
            ?? data_get($card, 'card_images.0.image_url')
            ?? self::PLACEHOLDER_IMAGE;
    }

    public function imageSmall(array $card): string
    {
        return $this->hostedImage($card, 'cards_small')
            ?? data_get($card, 'card_images.0.image_url_small')
            ?? $this->image($card);
    }

    /**
     * Where `php artisan yugioh:images` stores a card's image on the media disk.
     */
    public static function imagePath(int|string $id, string $size): string
    {
        return "yugioh/$size/$id.jpg";
    }

    /**
     * Our own copy of the image once it has been downloaded. YGOPRODeck asks that images are
     * re-hosted rather than hotlinked; until then the original URL is used.
     */
    private function hostedImage(array $card, string $size): ?string
    {
        if (empty($card['hosted_images'])) {
            return null;
        }

        return Storage::disk(config('filesystems.media'))->url(self::imagePath($card['id'], $size));
    }

    public function type(array $card): string
    {
        return $card['type'] ?? '';
    }

    public function subtype(array $card): string
    {
        return $card['race'] ?? '';
    }

    public function description(array $card): string
    {
        return $card['desc'] ?? '';
    }

    public function stats(array $card): array
    {
        $isLink = str_contains(strtolower($card['type'] ?? ''), 'link');

        return [
            'left' => ['label' => 'ATK', 'value' => $card['atk'] ?? null],
            'right' => $isLink
                ? ['label' => 'LINK', 'value' => $card['linkval'] ?? null]
                : ['label' => 'DEF', 'value' => $card['def'] ?? null],
        ];
    }

    /**
     * On a pack page the card carries that pack's `printing`; elsewhere its first printing is shown.
     */
    public function setName(array $card): ?string
    {
        return data_get($card, 'printing.set_name') ?? data_get($card, 'card_sets.0.set_name');
    }

    public function rarity(array $card): ?string
    {
        return data_get($card, 'printing.set_rarity') ?? data_get($card, 'card_sets.0.set_rarity');
    }

    public function price(array $card): ?string
    {
        return data_get($card, 'card_prices.0.cardmarket_price');
    }

    public function printSets(array $card): array
    {
        return collect($card['card_sets'] ?? [])
            ->map(fn ($set) => [
                'set_code' => $set['set_code'] ?? '',
                'set_name' => $set['set_name'] ?? 'Unknown',
            ])
            ->all();
    }

    /**
     * YGOPRODeck lists a card's status per banlist (`ban_tcg`, `ban_ocg`, ...); cards that aren't on it have no entry.
     */
    public function copyLimit(array $card, string $format): ?int
    {
        $status = data_get($card, "banlist_info.ban_$format");

        return is_string($status) ? (self::BANLIST_LIMITS[$status] ?? null) : null;
    }
}
