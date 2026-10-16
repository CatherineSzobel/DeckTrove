<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * The author can delete their comment, and a profile's owner can delete any comment on their profile.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return (int) $comment->user_id === $user->id
            || ($comment->commentable_type === $user->getMorphClass() && (int) $comment->commentable_id === $user->id);
    }
}
