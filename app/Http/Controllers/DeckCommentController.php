<?php

namespace App\Http\Controllers;

use App\Comments\CommentsRedirect;
use App\Http\Requests\StoreDeckCommentRequest;
use App\Models\Comment;
use App\Models\Deck;

class DeckCommentController extends Controller
{
    public function store(StoreDeckCommentRequest $request, Deck $deck)
    {
        Comment::post($request->user(), $deck, $request->validated('body'));

        return redirect()->to(CommentsRedirect::url($deck, keepPage: false))->with('success', 'Comment posted.');
    }
}
