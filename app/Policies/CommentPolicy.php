<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * The author can delete their comment, and the owner of what it's on (the profile's user, the deck's owner) can
     * delete any comment there.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return (int) $comment->user_id === $user->id
            || $comment->commentable?->commentOwnerId() === $user->id;
    }
}
