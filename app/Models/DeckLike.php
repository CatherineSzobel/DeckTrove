<?php

namespace App\Models;

use Database\Factories\DeckLikeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckLike extends Model
{
    /** @use HasFactory<DeckLikeFactory> */
    use HasFactory;

    // A like is only ever created or deleted, never updated.
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'deck_id'];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
