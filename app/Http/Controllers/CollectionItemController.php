<?php

namespace App\Http\Controllers;

use App\Collection\CardCollectionPanel;
use App\Collection\CollectionService;
use App\Http\Requests\CollectionCardRequest;
use App\Http\Requests\CollectionWantRequest;
use App\Models\CollectionItem;
use Illuminate\Validation\ValidationException;

/**
 * + and − on a card (the counters on tiles, the card page and the collection pages), and the card page's wishlist
 * heart. Without JS it goes back to the page it came from, at the card's tile (#card-<id>) or the card page's
 * collection row; the counters' JS (resources/js/collection/counter.js) asks for JSON instead and stays on the page.
 */
class CollectionItemController extends Controller
{
    public function __construct(private readonly CollectionService $collection) {}

    public function add(CollectionCardRequest $request)
    {
        return $this->change($request, fn () => $this->collection->add(
            $request->user(), $request->validated('game'), $request->validated('card'), $request->list(), $request->printing(),
        ), 'Added to your wishlist.', 'Added to your collection.');
    }

    public function remove(CollectionCardRequest $request)
    {
        return $this->change($request, fn () => $this->collection->remove(
            $request->user(), $request->validated('game'), $request->validated('card'), $request->list(), $request->printing(),
        ), 'Removed from your wishlist.', 'Removed from your collection.');
    }

    /** The card page's heart. Without JS it goes back to the card page's collection row. */
    public function want(CollectionWantRequest $request)
    {
        $wanted = $request->wants();

        try {
            $this->collection->setWanted($request->user(), $request->validated('game'), $request->validated('card'), $wanted);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->validator->errors()->first()], 422);
            }

            return redirect()->back()->withFragment('in-collection')->withErrors($e->errors(), 'collection');
        }

        $message = $wanted ? 'Added to your wishlist.' : 'Removed from your wishlist.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'wanted' => $wanted]);
        }

        return redirect()->back()->withFragment('in-collection')->with('success', $message);
    }

    /** Runs a change; JSON gets the message and the counter's new state, a redirect gets the notice. */
    private function change(CollectionCardRequest $request, callable $change, string $wishlistDone, string $collectionDone)
    {
        $message = $request->list() === CollectionItem::WANTED ? $wishlistDone : $collectionDone;

        try {
            $change();
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->validator->errors()->first()], 422);
            }

            return redirect()->back()->withFragment($this->tile($request))->withErrors($e->errors(), 'collection');
        }

        if ($request->expectsJson()) {
            [$user, $game, $card] = [$request->user(), $request->validated('game'), $request->validated('card')];
            $counts = $this->collection->cardCounts($user, $game, $card);

            return response()->json([
                'message' => $message,
                ...$this->collection->counterState($user, $game, $card, $request->list(), $request->printing()),
                // The card's totals, so the page's other counters for it (the card page row, the heart) can follow.
                'card' => [
                    'owned' => $counts['owned'],
                    'removable' => ($counts['printings'][''] ?? 0) > 0,
                    'wanted' => $counts['wanted'],
                    'note' => CardCollectionPanel::noteFor($counts),
                ],
            ]);
        }

        return redirect()->back()->withFragment($this->tile($request))->with('success', $message);
    }

    /** Where the page it came from scrolls back to: the card page section the counter sits in, else the card's tile. */
    private function tile(CollectionCardRequest $request): string
    {
        return $request->anchor() ?? 'card-'.$request->validated('card');
    }
}
