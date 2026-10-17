<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkDeckRequest;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * The Edit profile page's "Your decks" form: make several of your decks public or private, allow or turn off
 * comments on them, or delete them.
 */
class BulkDeckController extends Controller
{
    public function __invoke(BulkDeckRequest $request): RedirectResponse
    {
        $decks = $request->user()->decks()->whereKey($request->deckIds());
        $action = $request->validated('action');

        // Deck cards and likes go with a deck by the database's cascading deletes; comments have no foreign key, so
        // they go first.
        if ($action === 'delete') {
            Comment::deleteOnDecks($request->deckIds());
        }

        $count = match ($action) {
            'delete' => $decks->delete(),
            'public', 'private' => $decks->update(['is_public' => $action === 'public']),
            'comments_on', 'comments_off' => $decks->update(['allow_comments' => $action === 'comments_on']),
        };

        $done = match ($action) {
            'public' => 'made public',
            'private' => 'made private',
            'comments_on' => 'opened to comments',
            'comments_off' => 'closed to comments',
            'delete' => 'deleted',
        };

        return redirect()->to(route('profile.edit').'#your-decks')
            ->with('success', "{$count} ".Str::plural('deck', $count)." {$done}.");
    }
}
