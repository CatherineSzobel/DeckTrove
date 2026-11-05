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

    /** SQL for the copies with no printing, the only ones a tile's "−" takes (CollectionService::removable in PHP). */
    public const UNSPECIFIED_SUM = "SUM(CASE WHEN collection_items.printing = '' THEN collection_items.quantity ELSE 0 END)";

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

    /**
     * One row per card on a list, for tiles: card_key, game, copies and the card's external_id, name and image_url
     * (printings of a card share a key; MIN picks one of their cards). Callers add their own columns and order.
     */
    public function scopeCardTiles($query, string $list)
    {
        return $query->where('collection_items.list', $list)
            ->join('cards', 'cards.id', '=', 'collection_items.card_id')
            ->groupBy('collection_items.card_key', 'collection_items.game')
            ->selectRaw('collection_items.card_key, collection_items.game, SUM(collection_items.quantity) as copies, MIN(cards.external_id) as external_id, MIN(cards.name) as name, MIN(cards.image_url) as image_url');
    }
}
