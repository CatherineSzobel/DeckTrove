<?php

namespace App\Http\Controllers;

use App\Enums\BulkDeckAction;
use App\Http\Requests\BulkDeckRequest;
use App\Models\Comment;
use App\Models\Deck;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

        $tooSmall = 0;

        if ($action === BulkDeckAction::Public) {
            [$ready, $small] = $this->byMainSize($decks);
            $tooSmall = count($small);
            $decks = Deck::whereKey($ready);
        }

        $count = match ($action) {
            BulkDeckAction::Delete => $decks->delete(),
            BulkDeckAction::Public, BulkDeckAction::Private => $decks->update(['is_public' => $action === BulkDeckAction::Public]),
            BulkDeckAction::CommentsOn, BulkDeckAction::CommentsOff => $decks->update(['allow_comments' => $action === BulkDeckAction::CommentsOn]),
        };

        $redirect = redirect()->to(route('profile.edit').'#your-decks')
            ->with('success', "{$count} ".Str::plural('deck', $count)." {$action->doneLabel()}.");

        return $tooSmall
            ? $redirect->with('warning', "{$tooSmall} ".Str::plural('deck', $tooSmall).' stayed private: too few main deck cards to be public.')
            : $redirect;
    }

    /**
     * The decks' ids split into those with enough main deck cards to be public and those without, as the deck builder
     * rules it (DeckService::save).
     *
     * @return array{0: list<int>, 1: list<int>}
     */
    private function byMainSize(HasMany $decks): array
    {
        [$ready, $small] = $decks
            ->withSum(['deckCards as main_count' => fn ($cards) => $cards->where('zone', 'main')], 'count')
            ->get(['id', 'game'])
            ->partition(fn (Deck $deck) => (int) $deck->main_count >= config("series.{$deck->game}.deck.zones.main.min"));

        return [$ready->modelKeys(), $small->modelKeys()];
    }
}
