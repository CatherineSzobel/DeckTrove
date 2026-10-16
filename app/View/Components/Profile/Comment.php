<?php

namespace App\View\Components\Profile;

use App\Models\Comment as CommentModel;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * One comment on a profile (resources/views/components/profile/comment.blade.php): the author, when, a badge when the
 * profile owner wrote it, and the text, plus Delete for the author and the profile owner.
 */
class Comment extends Component
{
    public bool $byOwner;

    public string $ago;

    public string $iso;

    public string $date;

    public bool $canDelete;

    public function __construct(public CommentModel $comment, public User $profile)
    {
        $this->byOwner = (int) $comment->user_id === $profile->id;
        $this->ago = $comment->created_at->diffForHumans();
        $this->iso = $comment->created_at->toIso8601String();
        $this->date = $comment->created_at->format('j F Y, H:i');
        $this->canDelete = (bool) auth()->user()?->can('delete', $comment);
    }

    public function render(): View
    {
        return view('components.profile.comment');
    }
}
