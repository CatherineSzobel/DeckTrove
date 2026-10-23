<?php

namespace App\Http\Controllers;

use App\Collection\CollectionPage;
use App\Comments\CommentsRedirect;
use App\Models\CollectionItem;
use App\Models\User;
use App\Profiles\ProfilePage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

    /** The user’s collection, read-only: the owner, and others while collection_public is on. */
    public function collection(Request $request, User $user)
    {
        return $this->list($request, $user, CollectionItem::OWNED, 'viewCollection');
    }

    /** The user’s wishlist, read-only: the owner, and others while wishlist_public is on. */
    public function wishlist(Request $request, User $user)
    {
        return $this->list($request, $user, CollectionItem::WANTED, 'viewWishlist');
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

    private function list(Request $request, User $user, string $list, string $ability)
    {
        abort_unless(Gate::allows($ability, $user), 404);
        $page = CollectionPage::for($user, $request, $list, editable: false);

        return view('users.collection', [
            'user' => $user,
            'page' => $page,
            'seriesLinks' => $page->seriesLinks($list === CollectionItem::WANTED ? 'users.wishlist' : 'users.collection', ['user' => $user]),
        ]);
    }
}
