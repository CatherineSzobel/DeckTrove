<?php

namespace App\Collection;

use App\Models\CollectionItem;
use App\Models\User;
use App\Support\QueryInput;
use App\Support\Series;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * A collection or wishlist page (resources/views/collection/index.blade.php, and the public list pages): one tile per
 * card with its total copies, for one series, searchable by name. $editable shows − / + (your own /collection pages).
 */
final class CollectionPage
{
    public const PER_PAGE = 48;

    private ?LengthAwarePaginator $cards = null;

    private function __construct(
        private User $owner,
        private string $list,
        private string $series,
        private string $search,
        private bool $editable,
    ) {}

    public static function for(User $owner, Request $request, string $list, bool $editable): self
    {
        $search = mb_substr(trim(QueryInput::string($request, 'search')), 0, 100);

        return new self($owner, $list, Series::orDefault($request->query('series')), $search, $editable);
    }

    public function list(): string
    {
        return $this->list;
    }

    public function isWishlist(): bool
    {
        return $this->list === CollectionItem::WANTED;
    }

    /** "Collection" or "Wishlist". */
    public function listName(): string
    {
        return $this->isWishlist() ? 'Wishlist' : 'Collection';
    }

    /** The browser title: "Collection" on your own pages, "Wishlist · kaiba" on a profile’s. */
    public function title(?User $of = null): string
    {
        return $of ? $this->listName().' · '.$of->username : $this->listName();
    }

    /** The page’s heading on your own pages: "Your wishlist" / "Your collection". */
    public function heading(): string
    {
        return 'Your '.Str::lower($this->listName());
    }

    public function editable(): bool
    {
        return $this->editable;
    }

    public function series(): string
    {
        return $this->series;
    }

    public function search(): string
    {
        return $this->search;
    }

    public function isPublic(): bool
    {
        return $this->isWishlist() ? $this->owner->wishlist_public : $this->owner->collection_public;
    }

    /** "Only you can see your collection." / "Everyone can see your wishlist on your profile." */
    public function privacyLine(): string
    {
        $what = $this->isWishlist() ? 'wishlist' : 'collection';

        return $this->isPublic() ? "Everyone can see your $what on your profile." : "Only you can see your $what.";
    }

    /** Owned / Wishlist, keeping the series. */
    public function listLinks(): array
    {
        return [
            ['label' => 'Owned', 'href' => route('collection', ['series' => $this->series]), 'current' => ! $this->isWishlist()],
            ['label' => 'Wishlist', 'href' => route('collection.wishlist', ['series' => $this->series]), 'current' => $this->isWishlist()],
        ];
    }

    /** One link per series, keeping the list (and dropping the search). */
    public function seriesLinks(string $route, array $parameters = []): array
    {
        return collect(config('series'))->map(fn (array $config, string $series) => [
            'label' => $config['label'],
            'href' => route($route, [...$parameters, 'series' => $series]),
            'current' => $series === $this->series,
        ])->values()->all();
    }

    public function cards(): LengthAwarePaginator
    {
        return $this->cards ??= $this->owner->collectionItems()
            ->where('collection_items.list', $this->list)
            ->where('collection_items.game', $this->series)
            ->join('cards', 'cards.id', '=', 'collection_items.card_id')
            ->when($this->search !== '', fn ($query) => $query->where('cards.name', 'like', '%'.addcslashes($this->search, '%_\\').'%'))
            ->groupBy('collection_items.card_key', 'collection_items.game')
            ->selectRaw('collection_items.card_key, collection_items.game, SUM(collection_items.quantity) as copies, SUM(CASE WHEN collection_items.printing = \'\' THEN collection_items.quantity ELSE 0 END) as unspecified, MIN(cards.external_id) as external_id, MIN(cards.name) as name, MIN(cards.image_url) as image_url')
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(function ($row) {
                $row->copies = (int) $row->copies;
                $row->copies_label = self::copiesLabel($row->copies);
                $row->choose_printing_url = $this->choosePrintingUrl($row);
                // The tile's "−" has something to take unless every copy is in a printing (then the card page does it).
                $row->removable = $row->choose_printing_url === null;

                return $row;
            });
    }

    /**
     * Where to pick the printing to remove from, when every owned copy of the card is in a printing row (so "−" on the
     * tile has nothing unspecified to take); null otherwise, and always null on the wishlist.
     */
    private function choosePrintingUrl(object $row): ?string
    {
        if ($this->isWishlist() || (int) $row->unspecified > 0) {
            return null;
        }

        return route('cards.show', [$row->game, $row->external_id]).'#in-collection';
    }

    /**
     * The stat tiles: unique cards and copies, plus the wishlist's cost (for the whole wishlist, every series). A cost
     * with a null total is one that couldn't be worked out (Scryfall is down).
     *
     * @param  array{total: ?float, unpriced: int}|null  $cost
     */
    public function totals(?array $cost = null): array
    {
        $rows = $this->owner->collectionItems()->where('list', $this->list)->where('game', $this->series);
        $totals = [
            ['label' => 'Unique cards', 'value' => number_format((clone $rows)->distinct()->count('card_key'))],
            ['label' => 'Copies', 'value' => number_format((int) (clone $rows)->sum('quantity'))],
        ];

        if ($cost !== null) {
            $totals[] = ['label' => 'To buy', 'value' => $cost['total'] === null ? 'Price unavailable' : 'about '.config('app.currency.symbol').number_format($cost['total'], 2)];
        }

        return $totals;
    }

    /** "×3" under a tile. */
    public static function copiesLabel(int $copies): string
    {
        return '×'.$copies;
    }

    public function emptyTitle(): string
    {
        return $this->search !== '' ? 'No cards match “'.Str::limit($this->search, 40).'”' : 'No cards here yet';
    }
}
