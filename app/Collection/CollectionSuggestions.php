<?php

namespace App\Collection;

use App\Exceptions\ScryfallUnavailableException;
use App\Models\User;
use App\Services\CardService;
use App\ViewModels\CardViewModel;
use Illuminate\Support\Collection;

/**
 * Quick add on your own collection and wishlist pages (resources/views/collection/partials/suggestions.blade.php): the
 * card database's matches for the page's search that aren't on that list yet, each shown with a counter.
 */
final class CollectionSuggestions
{
    public const LIMIT = 8;

    /** @param  Collection<int, CardViewModel>  $cards */
    private function __construct(private CollectionPage $page, private Collection $cards, private bool $unavailable) {}

    public static function for(User $user, CollectionPage $page): self
    {
        if ($page->search() === '') {
            return new self($page, collect(), false);
        }

        $series = $page->series();
        $config = config("series.$series");

        try {
            $found = collect(app(CardService::class)->for($series)->search(['search' => $page->search()])->items())
                ->map(fn (array $card) => new CardViewModel($card, $config));
        } catch (ScryfallUnavailableException) {
            return new self($page, collect(), true);
        }

        $listed = $user->collectionItems()->forGame($series)->where('list', $page->list())
            ->whereIn('card_key', $found->map->collectionKey()->all())
            ->pluck('card_key')->flip();

        return new self($page, $found->reject(fn (CardViewModel $card) => $listed->has($card->collectionKey()))->take(self::LIMIT)->values(), false);
    }

    /** Only while searching: the strip answers a search. */
    public function shows(): bool
    {
        return $this->page->search() !== '';
    }

    /** @return Collection<int, CardViewModel> */
    public function cards(): Collection
    {
        return $this->cards;
    }

    public function unavailable(): bool
    {
        return $this->unavailable;
    }

    /** "Not in your collection" / "Not on your wishlist". */
    public function heading(): string
    {
        return $this->page->isWishlist() ? 'Not on your wishlist' : 'Not in your collection';
    }
}
