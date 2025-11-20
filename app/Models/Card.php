<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = ['game', 'external_id', 'name', 'stats', 'image_url'];

    protected $casts = [
        'stats' => 'array'
    ];

    public function decks()
    {
        return $this->belongsToMany(Deck::class, 'deck_cards');
    }
}
