<?php

namespace App\Models;

use Database\Factories\YugiohCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YugiohCard extends Model
{
    /** @use HasFactory<YugiohCardFactory> */
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['id', 'name', 'type', 'race', 'attribute', 'archetype', 'desc', 'data', 'images_hosted_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'images_hosted_at' => 'datetime'];
    }

    /**
     * The raw card as the rest of the app uses it, plus whether its images are hosted by us
     * (read by YugiohCardMapper).
     */
    public function toCardArray(): array
    {
        return $this->data + ['hosted_images' => $this->images_hosted_at !== null];
    }

    /**
     * The table columns for a raw YGOPRODeck card.
     */
    public static function attributesFrom(array $card): array
    {
        return [
            'id' => $card['id'],
            'name' => $card['name'],
            'type' => $card['type'] ?? '',
            'race' => $card['race'] ?? null,
            'attribute' => $card['attribute'] ?? null,
            'archetype' => $card['archetype'] ?? null,
            'desc' => $card['desc'] ?? null,
            'data' => $card,
        ];
    }
}
