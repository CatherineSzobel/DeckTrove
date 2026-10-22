<?php

namespace App\Models;

use Database\Factories\CollectionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Copies of one card in a user's collection (`owned`) or wishlist (`wanted`), for one printing ('' = unspecified).
 * A card's owned count is the sum of its owned rows; App\Collection\CollectionService keeps the rows consistent.
 */
class CollectionItem extends Model
{
    /** @use HasFactory<CollectionItemFactory> */
    use HasFactory;

    public const OWNED = 'owned';

    public const WANTED = 'wanted';

    protected $fillable = ['list', 'game', 'card_key', 'card_id', 'printing', 'quantity'];

    protected $attributes = ['list' => self::OWNED, 'printing' => ''];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function scopeOwned($query)
    {
        return $query->where('list', self::OWNED);
    }

    public function scopeWanted($query)
    {
        return $query->where('list', self::WANTED);
    }

    public function scopeForGame($query, string $game)
    {
        return $query->where('game', $game);
    }
}
