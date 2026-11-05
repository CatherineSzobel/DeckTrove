<?php

namespace App\Http\Controllers;

use App\Collection\CollectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** The pack page's "Add set to collection": one of each card, recorded with this set's printing. */
class PackCollectionController extends Controller
{
    public function __invoke(Request $request, CollectionService $collection, string $series, string $setCode, ?string $slug = null)
    {
        try {
            $added = $collection->addSet($request->user(), $series, $setCode, $slug);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors(), 'collection');
        }

        return back()->with('success', 'Added '.$added.' '.Str::plural('card', $added).' to your collection.');
    }
}
