<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YugiohCard extends Model
{
    /** @use HasFactory<\Database\Factories\YugiohCardFactory> */
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['id', 'name', 'type', 'race', 'attribute', 'archetype', 'desc', 'data'];

    protected function casts(): array
    {
        return ['data' => 'array'];
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
