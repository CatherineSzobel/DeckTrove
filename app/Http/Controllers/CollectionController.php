<?php

namespace App\Http\Controllers;

use App\Collection\CollectionPage;
use App\Collection\CollectionService;
use App\Collection\CollectionSuggestions;
use App\Exceptions\ScryfallUnavailableException;
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
        } catch (ScryfallUnavailableException) {
            $cost = ['total' => null, 'unpriced' => 0];
        }

        return $this->page($request, CollectionItem::WANTED, $cost);
    }

    private function page(Request $request, string $list, ?array $cost = null)
    {
        $page = CollectionPage::for($request->user(), $request, $list, editable: true);

        return view('collection.index', [
            'page' => $page,
            'suggestions' => CollectionSuggestions::for($request->user(), $page),
            'totals' => $page->totals($cost),
            'seriesLinks' => $page->seriesLinks($list === CollectionItem::WANTED ? 'collection.wishlist' : 'collection'),
        ]);
    }
}
