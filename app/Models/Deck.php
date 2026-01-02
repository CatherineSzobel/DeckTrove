<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Deck extends Model
{
    protected $fillable = ['user_id', 'game', 'name', 'description', 'is_public', 'image'];

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'deck_cards')
            ->using(DeckCard::class)
            ->withPivot('zone', 'count')
            ->withTimestamps();
    }

    // Add this relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
