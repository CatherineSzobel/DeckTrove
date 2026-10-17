<?php

namespace App\Models;

use App\Comments\Commentable;
use Database\Factories\DeckFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Deck extends Model implements Commentable
{
    /** @use HasFactory<DeckFactory> */
    use HasFactory;

    protected $fillable = ['game', 'name', 'description', 'is_public', 'allow_comments', 'image', 'format'];

    /** New decks allow comments, as the column's default (so an unsaved or just-created deck says so too). */
    protected $attributes = ['allow_comments' => true];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'allow_comments' => 'boolean',
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

    public function likes(): HasMany
    {
        return $this->hasMany(DeckLike::class);
    }

    /** Comments players left on this deck; each knows its deck without a query (for CommentPolicy). */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->chaperone('commentable');
    }

    public function commentOwnerId(): int
    {
        return (int) $this->user_id;
    }

    /** Only public decks with comments on take (and show) comments; the owner still sees them read-only. */
    public function acceptsComments(): bool
    {
        return $this->is_public && $this->allow_comments;
    }

    public function commentsFallbackUrl(): string
    {
        return route('decks.show', $this);
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

    /**
     * "Casual" for a deck without a format, otherwise the format's label from the series config.
     */
    protected function formatLabel(): Attribute
    {
        return Attribute::get(fn () => $this->format
            ? config("series.{$this->game}.deck.formats.{$this->format}.label", $this->format)
            : 'Casual');
    }

    /**
     * The series' name, e.g. "Yu-Gi-Oh!".
     */
    protected function seriesLabel(): Attribute
    {
        return Attribute::get(fn () => config("series.{$this->game}.label", ucfirst((string) $this->game)));
    }

    /**
     * "Public" or "Private", for the dashboard's deck tiles.
     */
    protected function visibilityLabel(): Attribute
    {
        return Attribute::get(fn () => $this->is_public ? 'Public' : 'Private');
    }

    /**
     * A tooltip for the format badge: what the deck's format means.
     */
    protected function formatTitle(): Attribute
    {
        return Attribute::get(fn () => $this->format
            ? "Built for {$this->format_label}."
            : 'No format: only the normal deck rules apply.');
    }

    public function scopeWithCardCount($query)
    {
        return $query->withSum('deckCards as card_count', 'count');
    }
}
