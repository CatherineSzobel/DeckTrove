<?php

namespace App\Http\Controllers;

use App\Collection\CollectionService;
use App\Models\Deck;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** The deck page's "Add deck to collection" and "Add missing to wishlist" (both top up, so a second click adds nothing). */
class DeckCollectionController extends Controller
{
    public function __construct(private readonly CollectionService $collection) {}

    public function collection(Request $request, Deck $deck)
    {
        try {
            $added = $this->collection->topUpDeck($request->user(), $deck);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors(), 'collection');
        }

        return back()->with('success', $added
            ? 'Added '.$added.' '.Str::plural('copy', $added).' to your collection.'
            : 'You already own everything in this deck.');
    }

    public function wishlist(Request $request, Deck $deck)
    {
        try {
            $added = $this->collection->wishMissing($request->user(), $deck);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors(), 'collection');
        }

        return back()->with('success', $added
            ? 'Added '.$added.' '.Str::plural('copy', $added).' to your wishlist.'
            : 'Nothing is missing from this deck.');
    }
}
