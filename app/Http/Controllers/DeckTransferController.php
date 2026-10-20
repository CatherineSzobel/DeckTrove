<?php

namespace App\Http\Controllers;

use App\Decks\DeckFormat;
use App\Decks\ImportedDeck;
use App\Http\Requests\ImportDeckRequest;
use App\Models\Deck;
use App\Services\CardService;
use App\Services\DeckService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Moving decks in and out: export to other tools' formats, import from them, and copy public decks.
 */
class DeckTransferController extends Controller
{
    public function __construct(
        protected DeckService $deckService,
        protected CardService $cardService,
    ) {}

    public function export(Deck $deck)
    {
        Gate::authorize('view', $deck);

        $format = $this->format($deck->game);
        $zones = $this->deckService->zoneCounts($deck);
        $ids = collect($zones)->flatMap(fn ($zone) => array_keys($zone))->unique()->all();

        // Fall back to the locally stored name for cards the source no longer has.
        $cards = $this->cardService->for($deck->game)->findMany($ids);
        foreach ($deck->cards as $card) {
            if (! $cards->has($card->external_id)) {
                $cards->put($card->external_id, ['id' => $card->external_id, 'name' => $card->name]);
            }
        }

        $filename = (Str::slug($deck->name) ?: 'deck').'.'.$format->extension();

        return response()->streamDownload(
            function () use ($format, $zones, $cards) {
                echo $format->export($zones, $cards);
            },
            $filename,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    public function import(ImportDeckRequest $request, string $series)
    {
        $imported = $this->readImport($request, $series);

        $deck = $request->user()->decks()->make(['game' => $series]);

        // Saving applies the same rules as the deck builder (zones, sizes, copy limits).
        $this->deckService->save($deck, ['deck_title' => $request->deckName() ?? 'Imported deck', 'is_public' => false], $imported->counts);

        return $this->withSkippedCards(
            redirect()->route('decks.edit', $deck)->with('success', 'Deck imported. It is private until you make it public.'),
            $imported,
        );
    }

    /**
     * Replaces an existing deck's cards with an imported list, e.g. to fix a wrong import. Like any import the deck
     * becomes Casual; an uploaded file also renames the deck after the file, and the old cover is dropped.
     */
    public function reimport(ImportDeckRequest $request, Deck $deck)
    {
        $imported = $this->readImport($request, $deck->game);

        $this->deckService->save($deck, [
            'deck_title' => $request->deckName() ?? $deck->name,
            'deck_description' => $deck->description,
            'image' => null,
            'is_public' => $deck->is_public,
            'format' => null,
        ], $imported->counts);

        return $this->withSkippedCards(
            redirect()->route('decks.edit', $deck)->with('success', 'Deck replaced with the imported list.'),
            $imported,
        );
    }

    /**
     * The submitted deck read in the series' format. A list without any cards we know fails like a validation error.
     */
    private function readImport(ImportDeckRequest $request, string $series): ImportedDeck
    {
        $imported = $this->format($series)->import($request->deckText());

        if ($imported->isEmpty()) {
            throw ValidationException::withMessages(['list' => 'We could not find any cards in that deck list.']);
        }

        return $imported;
    }

    private function withSkippedCards(RedirectResponse $redirect, ImportedDeck $imported): RedirectResponse
    {
        if ($imported->missing) {
            $redirect->with('warning', 'These cards could not be found and were skipped: '.implode(', ', array_slice($imported->missing, 0, 20))
                .(count($imported->missing) > 20 ? ' and '.(count($imported->missing) - 20).' more.' : '.'));
        }

        return $redirect;
    }

    public function copy(Request $request, Deck $deck)
    {
        Gate::authorize('view', $deck);

        $copy = $this->deckService->copy($deck, $request->user());

        return redirect()->route('decks.edit', $copy)->with('success', 'Deck copied to your decks. Your copy is private.');
    }

    private function format(string $series): DeckFormat
    {
        return app(config("series.$series.deck_format"));
    }
}
