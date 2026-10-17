<?php

namespace App\Http\Controllers;

use App\Comments\CommentsRedirect;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * Deletes a comment (CommentPolicy::delete) and goes back to the page it was on: the profile or the deck.
     */
    public function destroy(Comment $comment)
    {
        $target = $comment->commentable;
        $comment->delete();

        return redirect()->to(CommentsRedirect::url($target))->with('success', 'Comment deleted.');
    }
}
