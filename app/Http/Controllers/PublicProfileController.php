<?php

namespace App\Http\Controllers;

use App\Decks\PublicDeckQuery;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * A user's public profile: avatar, about, their public decks and the likes those decks received. Never the email,
 * and never private decks, also not for the owner (who edits their account at /profile).
 */
class PublicProfileController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        $query = PublicDeckQuery::fromRequest($request)->forUser($user);

        return view('users.show', [
            'user' => $user,
            'decks' => $query->paginate(12),
            'stats' => [
                'public_decks' => $user->decks()->public()->count(),
                'likes_received' => DeckLike::query()
                    ->whereHas('deck', fn ($deck) => $deck->public()->where('user_id', $user->id))
                    ->count(),
            ],
            'isOwner' => $request->user()?->is($user) ?? false,
            'filters' => $query->filters(),
            'sorts' => PublicDeckQuery::SORTS,
        ]);
    }
}
