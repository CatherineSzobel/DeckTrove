<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deck extends Model
{
    protected $fillable = ['user_id', 'game', 'name', 'description'];

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'deck_cards')
                    ->withPivot('count', 'zone')
                    ->withTimestamps();
    }
}
