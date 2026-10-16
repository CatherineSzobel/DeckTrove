<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileCommentRequest;
use App\Models\Comment;
use App\Models\User;
use App\Profiles\CommentsRedirect;

class ProfileCommentController extends Controller
{
    public function store(StoreProfileCommentRequest $request, User $user)
    {
        $comment = new Comment(['body' => $request->validated('body')]);
        $comment->author()->associate($request->user());
        $comment->commentable()->associate($user);
        $comment->save();

        return redirect()->to(CommentsRedirect::url($user, keepPage: false))->with('success', 'Comment posted.');
    }
}
