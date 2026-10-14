<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkDeckRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * The Edit profile page's "Your decks" form: make several of your decks public or private, or delete them.
 */
class BulkDeckController extends Controller
{
    public function __invoke(BulkDeckRequest $request): RedirectResponse
    {
        $decks = $request->user()->decks()->whereKey($request->deckIds());
        $action = $request->validated('action');

        // Deck cards and likes go with a deck by the database's cascading deletes, as in DeckController::destroy.
        $count = $action === 'delete' ? $decks->delete() : $decks->update(['is_public' => $action === 'public']);

        $done = match ($action) {
            'public' => 'made public',
            'private' => 'made private',
            'delete' => 'deleted',
        };

        return redirect()->to(route('profile.edit').'#your-decks')
            ->with('success', "{$count} ".Str::plural('deck', $count)." {$done}.");
    }
}
