<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeckCard extends Model
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
