<?php

namespace App\Http\Controllers;

use App\Collection\CollectionPage;
use App\Collection\CollectionService;
use App\Collection\CollectionSuggestions;
use App\Exceptions\CardSourceUnavailableException;
use App\Models\CollectionItem;
use Illuminate\Http\Request;

/**
 * Your own collection (/collection) and wishlist (/collection/wishlist), with − / + on each card and, while searching,
 * the card database's matches you can add.
 */
class CollectionController extends Controller
{
    public function owned(Request $request)
    {
        return $this->page($request, CollectionItem::OWNED);
    }

    public function wishlist(Request $request, CollectionService $collection)
    {
        try {
            $cost = $collection->wishlistCost($request->user());
        } catch (CardSourceUnavailableException) {
            $cost = ['total' => null, 'unpriced' => 0];
        }

        return $this->page($request, CollectionItem::WANTED, $cost);
    }

    private function page(Request $request, string $list, ?array $cost = null)
    {
        $page = CollectionPage::for($request->user(), $request, $list, editable: true);

        // The page's script asks for just the totals after a − / + (resources/js/collection/totals.js).
        if ($request->ajax()) {
            return response()->view('collection.partials.totals', ['totals' => $page->totals($cost)])->header('Vary', 'X-Requested-With');
        }

        return view('collection.index', [
            'page' => $page,
            'suggestions' => CollectionSuggestions::for($request->user(), $page),
            'totals' => $page->totals($cost),
            'seriesLinks' => $page->seriesLinks($list === CollectionItem::WANTED ? 'collection.wishlist' : 'collection'),
        ]);
    }
}
