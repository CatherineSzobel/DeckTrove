<?php

namespace App\Collection;

use App\Models\CollectionItem;
use App\Models\User;
use App\Support\Money;
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
        $search = QueryInput::search($request, 'search');

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
        return Series::links(fn (string $series) => route($route, [...$parameters, 'series' => $series]), $this->series);
    }

    public function cards(): LengthAwarePaginator
    {
        return $this->cards ??= $this->owner->collectionItems()
            ->cardTiles($this->list)
            ->where('collection_items.game', $this->series)
            ->when($this->search !== '', fn ($query) => $query->where('cards.name', 'like', QueryInput::like($this->search)))
            ->selectRaw(CollectionItem::UNSPECIFIED_SUM.' as unspecified')
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
        $sums = $this->owner->collectionItems()->where('list', $this->list)->forGame($this->series)
            ->selectRaw('COUNT(DISTINCT card_key) as cards, SUM(quantity) as copies')->first();
        $totals = [
            ['value' => number_format((int) $sums->cards), 'label' => Str::plural('unique card', (int) $sums->cards), 'warn' => false],
            ['value' => number_format((int) $sums->copies), 'label' => Str::plural('copy', (int) $sums->copies), 'warn' => false],
        ];

        if ($cost !== null) {
            $totals[] = $cost['total'] === null
                ? ['value' => '—', 'label' => 'price unavailable', 'warn' => true]
                : ['value' => Money::format($cost['total']), 'label' => 'to buy (about)', 'warn' => true];
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
