<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Local reference to a card from an external TCG source, so decks can point at it.
 */
class Card extends Model
{
    protected $fillable = ['game', 'external_id', 'name', 'image_url'];

    public function decks(): BelongsToMany
    {
        return $this->belongsToMany(Deck::class, 'deck_cards')
            ->using(DeckCard::class)
            ->withPivot('count', 'zone')
            ->withTimestamps();
    }
}
