<?php

namespace App\View\Components;

use App\Models\Comment as CommentModel;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * One comment on a profile or deck (resources/views/components/comment.blade.php): the author, when, a badge when the
 * owner of what it's on wrote it ("Profile owner", "Deck owner"), and the text, plus Delete for who may. The target
 * comes from the comment (chaperoned by the profile's and deck's relations, so no query).
 */
class Comment extends Component
{
    public bool $byOwner;

    public string $ownerBadge;

    public string $ago;

    public string $iso;

    public string $date;

    public bool $canDelete;

    public function __construct(public CommentModel $comment)
    {
        $target = $comment->commentable;
        $this->byOwner = $target !== null && (int) $comment->user_id === $target->commentOwnerId();
        $this->ownerBadge = $target?->commentOwnerLabel() ?? '';
        $this->ago = $comment->created_at->diffForHumans();
        $this->iso = $comment->created_at->toIso8601String();
        $this->date = $comment->created_at->format('j F Y, H:i');
        $this->canDelete = (bool) auth()->user()?->can('delete', $comment);
    }

    public function render(): View
    {
        return view('components.comment');
    }
}
