<?php

namespace App\Models;

use Database\Factories\DeckFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    /** @use HasFactory<DeckFactory> */
    use HasFactory;

    protected $fillable = ['game', 'name', 'description', 'is_public', 'image', 'format'];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class, 'deck_cards')
            ->using(DeckCard::class)
            ->withPivot('zone', 'count')
            ->withTimestamps();
    }

    public function deckCards(): HasMany
    {
        return $this->hasMany(DeckCard::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Total number of cards (including duplicates). Use withCardCount() to avoid N+1 queries in lists.
     */
    protected function cardCount(): Attribute
    {
        return Attribute::get(fn ($value) => (int) ($value ?? $this->deckCards()->sum('count')));
    }

    public function scopeWithCardCount($query)
    {
        return $query->withSum('deckCards as card_count', 'count');
    }
}
