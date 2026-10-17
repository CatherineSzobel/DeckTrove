<?php

namespace App\Http\Controllers;

use App\Comments\CommentsRedirect;
use App\Models\User;
use App\Profiles\ProfilePage;
use Illuminate\Http\Request;

/**
 * A user's public profile and its list pages (App\Profiles\ProfilePage). Never the email, and never private decks,
 * also not for the owner (who edits their profile at /profile/edit).
 */
class PublicProfileController extends Controller
{
    public function show(Request $request, User $user)
    {
        return view('users.show', ['user' => $user, 'page' => ProfilePage::for($user, $request)]);
    }

    /** All of the user's public decks. */
    public function decks(Request $request, User $user)
    {
        return view('users.decks', ['user' => $user, 'page' => ProfilePage::for($user, $request, 'decks')]);
    }

    /** All public decks the user liked: the owner, and others while show_liked_decks is on. */
    public function liked(Request $request, User $user)
    {
        $page = ProfilePage::for($user, $request, 'liked');
        abort_unless($page->showsLiked(), 404);

        return view('users.liked', ['user' => $user, 'page' => $page]);
    }

    /** All comments on the profile, with the form, while allow_comments is on. */
    public function comments(Request $request, User $user)
    {
        $page = ProfilePage::for($user, $request, 'comments');
        abort_unless($page->showsComments(), 404);

        if ($lastPage = CommentsRedirect::pastLastPage($page->comments())) {
            return redirect()->to($lastPage);
        }

        return view('users.comments', ['user' => $user, 'page' => $page]);
    }
}
