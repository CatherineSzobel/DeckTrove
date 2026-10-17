<?php

namespace App\Decks;

use App\Comments\CommentDraft;
use App\Comments\CommentsRedirect;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The Comments section of a deck page (resources/views/decks/partials/comments.blade.php). Open while the deck is
 * public and allows comments; otherwise only the owner sees the comments, read-only with a note saying why, and
 * nobody sees the section when there are none.
 */
final class DeckComments
{
    public const PER_PAGE = 10;

    private ?LengthAwarePaginator $comments = null;

    private function __construct(private Deck $deck, private ?User $viewer, private CommentDraft $draft) {}

    public static function for(Deck $deck, Request $request): self
    {
        return new self($deck, $request->user(), CommentDraft::fromOld($request));
    }

    public function isOwner(): bool
    {
        return $this->viewer !== null && $this->viewer->id === $this->deck->commentOwnerId();
    }

    public function isOpen(): bool
    {
        return $this->deck->acceptsComments();
    }

    public function isReadOnly(): bool
    {
        return ! $this->isOpen() && $this->isOwner();
    }

    public function visible(): bool
    {
        return $this->isOpen() || ($this->isReadOnly() && $this->count() > 0);
    }

    public function canPost(): bool
    {
        return $this->isOpen() && $this->viewer !== null;
    }

    /** Why the owner sees the comments read-only; private wins over turned off. */
    public function note(): ?string
    {
        if (! $this->isReadOnly()) {
            return null;
        }

        return $this->deck->is_public
            ? 'Comments are turned off for this deck. Other players can’t see or add them.'
            : 'Comments are hidden while this deck is private.';
    }

    /** The turned-off note ends with an "Edit deck" link. */
    public function showsEditLink(): bool
    {
        return $this->isReadOnly() && $this->deck->is_public;
    }

    /** The paginator's total, so the page counts the comments once. */
    public function count(): int
    {
        return $this->comments()->total();
    }

    /** "1 comment", "12 comments". */
    public function headerLabel(): string
    {
        return $this->count().' '.Str::plural('comment', $this->count());
    }

    /** The comments, newest first, 10 per page; page links land on #comments. */
    public function comments(): LengthAwarePaginator
    {
        return $this->comments ??= $this->deck->comments()
            ->newest()
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->fragment('comments');
    }

    /** The last page's URL when ?page= is past it and there are comments, else null. */
    public function lastPageUrl(): ?string
    {
        return CommentsRedirect::pastLastPage($this->comments());
    }

    public function draft(): CommentDraft
    {
        return $this->draft;
    }
}
