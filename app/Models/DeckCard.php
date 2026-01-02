<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DeckCard extends Pivot
{
    protected $table = 'deck_cards';

    protected $fillable = ['deck_id', 'card_id', 'zone', 'count'];

    public function deck()
    {
        return $this->belongsTo(Deck::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
