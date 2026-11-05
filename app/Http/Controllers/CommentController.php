<?php

namespace App\Http\Controllers;

use App\Comments\CommentsRedirect;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Deck;
use App\Models\User;

class CommentController extends Controller
{
    /**
     * Posts a comment on a profile or a deck (StoreCommentRequest checks it accepts comments) and shows it. The two
     * models are only named here so the router binds whichever one the route has.
     */
    public function store(StoreCommentRequest $request, ?User $user = null, ?Deck $deck = null)
    {
        $target = $request->target();
        Comment::post($request->user(), $target, $request->validated('body'));

        return redirect()->to(CommentsRedirect::url($target, keepPage: false))->with('success', 'Comment posted.');
    }

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
