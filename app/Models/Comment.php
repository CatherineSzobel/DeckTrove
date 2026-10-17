<?php

namespace App\Models;

use App\Comments\Commentable;
use Database\Factories\CommentFactory;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A comment on a profile (commentable_type 'user') or a deck ('deck'). Plain text, never edited.
 * The author and the target are set through the relations, never mass-assigned.
 */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['body'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Deletes the comments on these decks. Comments have no foreign key to their target, and decks are also deleted
     * by query (bulk) and by the database (with their owner), so every deck delete calls this first. $deckIds can be
     * a query selecting the ids, so the delete is one statement.
     */
    public static function deleteOnDecks(iterable|Builder $deckIds): int
    {
        return static::query()
            ->where('commentable_type', (new Deck)->getMorphClass())
            ->whereIn('commentable_id', $deckIds instanceof Builder ? $deckIds : collect($deckIds))
            ->delete();
    }

    /** Newest first (the id breaks ties within a second), with their authors. */
    public function scopeNewest($query)
    {
        return $query->with('author')->latest()->orderByDesc('id');
    }

    /** Saves a comment by $author on a profile or deck. */
    public static function post(User $author, Commentable $target, string $body): self
    {
        $comment = new self(['body' => $body]);
        $comment->author()->associate($author);
        $comment->commentable()->associate($target);
        $comment->save();

        return $comment;
    }
}
