<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Profiles\CommentsRedirect;

class CommentController extends Controller
{
    /**
     * Deletes a comment (CommentPolicy::delete) and goes back to the page it was on. Every comment is on a
     * profile for now; deck comments will need their own way back.
     */
    public function destroy(Comment $comment)
    {
        $profile = $comment->commentable;
        $comment->delete();

        return redirect()->to(CommentsRedirect::url($profile))->with('success', 'Comment deleted.');
    }
}
