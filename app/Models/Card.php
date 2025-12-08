<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = ['game', 'external_id', 'name', 'type', 'subtype', 'image_url', 'stats'];

    protected $casts = [
        'stats' => 'array'
    ];

    public function decks()
    {
        // Include pivot fields for count & zone
        return $this->belongsToMany(Deck::class, 'deck_cards')
            ->withPivot('count', 'zone')
            ->withTimestamps();
    }
}
