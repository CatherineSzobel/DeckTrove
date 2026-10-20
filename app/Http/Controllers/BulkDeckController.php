<?php

namespace App\Http\Controllers;

use App\Enums\BulkDeckAction;
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
        $action = $request->action();

        // Deck cards and likes go with a deck by the database's cascading deletes; comments have no foreign key, and a
        // delete by query runs no model events, so they go first.
        if ($action === BulkDeckAction::Delete) {
            Comment::deleteOnDecks($request->deckIds());
        }

        $count = match ($action) {
            BulkDeckAction::Delete => $decks->delete(),
            BulkDeckAction::Public, BulkDeckAction::Private => $decks->update(['is_public' => $action === BulkDeckAction::Public]),
            BulkDeckAction::CommentsOn, BulkDeckAction::CommentsOff => $decks->update(['allow_comments' => $action === BulkDeckAction::CommentsOn]),
        };

        return redirect()->to(route('profile.edit').'#your-decks')
            ->with('success', "{$count} ".Str::plural('deck', $count)." {$action->doneLabel()}.");
    }
}
