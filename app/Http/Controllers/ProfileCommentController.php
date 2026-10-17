<?php

namespace App\Http\Controllers;

use App\Comments\CommentsRedirect;
use App\Http\Requests\StoreProfileCommentRequest;
use App\Models\Comment;
use App\Models\User;

class ProfileCommentController extends Controller
{
    public function store(StoreProfileCommentRequest $request, User $user)
    {
        Comment::post($request->user(), $user, $request->validated('body'));

        return redirect()->to(CommentsRedirect::url($user, keepPage: false))->with('success', 'Comment posted.');
    }
}
