<?php

namespace App\View\Components;

use App\Models\Comment as CommentModel;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * One comment on a profile or deck (resources/views/components/comment.blade.php): the author, when, a badge when the
 * owner of what it's on wrote it ($ownerBadge: "Profile owner", "Deck owner"), and the text, plus Delete for who may.
 */
class Comment extends Component
{
    public bool $byOwner;

    public string $ago;

    public string $iso;

    public string $date;

    public bool $canDelete;

    public function __construct(public CommentModel $comment, public int $ownerId, public string $ownerBadge)
    {
        $this->byOwner = (int) $comment->user_id === $ownerId;
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
