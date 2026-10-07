<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DeckCard extends Pivot
{
    protected $table = 'deck_cards';

    public $incrementing = true;

    protected $fillable = ['deck_id', 'card_id', 'zone', 'count'];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
