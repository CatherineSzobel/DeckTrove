<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\DeckLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Likes on public decks. Both actions are idempotent. JSON requests (the like button's fetch) get the new state and
 * count; form posts go back to the page they came from.
 */
class DeckLikeController extends Controller
{
    public function store(Request $request, Deck $deck): JsonResponse|RedirectResponse
    {
        // Ignored when the like already exists, so a double click can't hit the unique index.
        DeckLike::query()->insertOrIgnore([
            'user_id' => $request->user()->id,
            'deck_id' => $deck->id,
            'created_at' => now(),
        ]);

        return $this->respond($request, $deck, true);
    }

    public function destroy(Request $request, Deck $deck): JsonResponse|RedirectResponse
    {
        $deck->likes()->where('user_id', $request->user()->id)->delete();

        return $this->respond($request, $deck, false);
    }

    private function respond(Request $request, Deck $deck, bool $liked): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['liked' => $liked, 'likes_count' => $deck->likes()->count()]);
        }

        return redirect()->back(fallback: route('decks.show', $deck));
    }
}
