<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deck extends Model
{
    protected $fillable = ['user_id', 'game', 'name', 'description', 'is_public', 'image'];

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'deck_cards')
            ->withPivot('count', 'zone')
            ->withTimestamps();
    }
}
