<?php

namespace App\Profiles;

use App\Collection\CollectionPage;
use App\Comments\CommentDraft;
use App\Decks\PublicDeckQuery;
use App\Models\CollectionItem;
use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * A public profile and its list pages. The overview (users/show.blade.php) has the profile card with jump links and
 * short sections: Public decks, Liked decks (the owner, and others while show_liked_decks is on), Collection and
 * Wishlist (the owner, and others while collection_public / wishlist_public is on) and Comments (while allow_comments
 * is on), each linking to its full list page (users/decks, liked, collection, wishlist, comments). Only what the page
 * shows is queried.
 */
final class ProfilePage
{
    public const DECKS_PER_PAGE = 12;

    public const COMMENTS_PER_PAGE = 20;

    public const OVERVIEW_DECKS = 6;

    public const OVERVIEW_LIKED = 8;

    public const OVERVIEW_COMMENTS = 5;

    public const OVERVIEW_COLLECTION = 8;

    private const HEADINGS = ['decks' => 'Public decks', 'liked' => 'Liked decks', 'collection' => 'Collection', 'wishlist' => 'Wishlist', 'comments' => 'Comments'];

    /** @var array<string, int> */
    private array $counts = [];

    private ?int $likesReceived = null;

    private ?PublicDeckQuery $deckQuery = null;

    private ?LengthAwarePaginator $decks = null;

    private ?LengthAwarePaginator $comments = null;

    /** @var array<string, Collection> */
    private array $overview = [];

    private function __construct(
        private User $profile,
        private ?User $viewer,
        private PublicDeckQuery $baseQuery,
        private string $list,
        private CommentDraft $commentDraft,
    ) {}

    /**
     * $list: 'overview' (the profile itself) or a list page: 'decks', 'liked' or 'comments'. The controller checks
     * that a list page may be seen.
     */
    public static function for(User $profile, Request $request, string $list = 'overview'): self
    {
        return new self($profile, $request->user(), PublicDeckQuery::fromRequest($request), $list, CommentDraft::fromOld($request));
    }

    public function user(): User
    {
        return $this->profile;
    }

    public function list(): string
    {
        return $this->list;
    }

    public function isOwner(): bool
    {
        return $this->viewer?->is($this->profile) ?? false;
    }

    /** Liked decks show to the owner, and to others while show_liked_decks is on. */
    public function showsLiked(): bool
    {
        return $this->isOwner() || $this->profile->show_liked_decks;
    }

    /** The collection shows to the owner, and to others while collection_public is on. */
    public function showsCollection(): bool
    {
        return Gate::forUser($this->viewer)->allows('viewCollection', $this->profile);
    }

    /** The wishlist shows to the owner, and to others while wishlist_public is on. */
    public function showsWishlist(): bool
    {
        return Gate::forUser($this->viewer)->allows('viewWishlist', $this->profile);
    }

    /** The owner sees their collection while others can't. */
    public function collectionIsPrivate(): bool
    {
        return $this->showsCollection() && ! $this->profile->collection_public;
    }

    /** The owner sees their wishlist while others can't. */
    public function wishlistIsPrivate(): bool
    {
        return $this->showsWishlist() && ! $this->profile->wishlist_public;
    }

    /** Comments show (and can be posted) while allow_comments is on. */
    public function showsComments(): bool
    {
        return $this->profile->acceptsComments();
    }

    /** The owner sees their liked decks while others can't. */
    public function likedIsPrivate(): bool
    {
        return $this->showsLiked() && ! $this->profile->show_liked_decks;
    }

    public function canComment(): bool
    {
        return $this->viewer !== null;
    }

    /** A list page's heading; the overview is named by the username. */
    public function heading(): string
    {
        return self::HEADINGS[$this->list] ?? $this->profile->username;
    }

    /** A page's title, e.g. "Liked decks · kaiba"; just the username on the overview. */
    public function title(): string
    {
        return $this->list === 'overview' ? $this->profile->username : $this->heading().' · '.$this->profile->username;
    }

    public function deckCount(): int
    {
        return $this->count('decks');
    }

    /** Liked decks that are still public. */
    public function likedCount(): int
    {
        return $this->count('liked');
    }

    public function commentCount(): int
    {
        return $this->count('comments');
    }

    /** How many items the current list page has in total (before the series filter). */
    public function listCount(): int
    {
        return $this->count($this->list);
    }

    /** Likes on the user's public decks. */
    public function likesReceived(): int
    {
        return $this->likesReceived ??= DeckLike::query()
            ->whereHas('deck', fn ($deck) => $deck->public()->where('user_id', $this->profile->id))
            ->count();
    }

    /**
     * The profile card's jump links to the overview's sections, visible sections only.
     *
     * @return list<array{id: string, label: string, count: int}>
     */
    public function sections(): array
    {
        $sections = [['id' => 'decks', 'label' => 'Public decks', 'count' => $this->deckCount()]];

        if ($this->showsLiked()) {
            $sections[] = ['id' => 'liked', 'label' => 'Liked decks', 'count' => $this->likedCount()];
        }

        if ($this->showsCollection()) {
            $sections[] = ['id' => 'collection', 'label' => 'Collection', 'count' => $this->count('collection')];
        }

        if ($this->showsWishlist()) {
            $sections[] = ['id' => 'wishlist', 'label' => 'Wishlist', 'count' => $this->count('wishlist')];
        }

        if ($this->showsComments()) {
            $sections[] = ['id' => 'comments', 'label' => 'Comments', 'count' => $this->commentCount()];
        }

        return $sections;
    }

    /** The newest public decks for the overview. */
    public function overviewDecks(): Collection
    {
        return $this->overview['decks'] ??= PublicDeckQuery::defaults($this->viewer)
            ->forUser($this->profile)
            ->take(self::OVERVIEW_DECKS);
    }

    /** Liked public decks for the overview, newest deck first. */
    public function overviewLiked(): Collection
    {
        return $this->overview['liked'] ??= PublicDeckQuery::defaults($this->viewer)
            ->likedBy($this->profile)
            ->take(self::OVERVIEW_LIKED);
    }

    /** The newest cards on the collection, for the overview: one row per card with its copies. */
    public function overviewCollection(): Collection
    {
        return $this->overview['collection'] ??= $this->listOverview(CollectionItem::OWNED);
    }

    public function overviewWishlist(): Collection
    {
        return $this->overview['wishlist'] ??= $this->listOverview(CollectionItem::WANTED);
    }

    /** The newest comments for the overview. */
    public function overviewComments(): Collection
    {
        return $this->overview['comments'] ??= $this->profile->profileComments()
            ->newest()
            ->limit(self::OVERVIEW_COMMENTS)
            ->get();
    }

    /**
     * The "See all" link of an overview section ('decks', 'liked', 'comments'), or null when everything fits.
     *
     * @return array{label: string, href: string}|null
     */
    public function moreLink(string $section): ?array
    {
        return match ($section) {
            'decks' => $this->deckCount() > self::OVERVIEW_DECKS
                ? ['label' => "All {$this->deckCount()} decks", 'href' => route('users.decks', $this->profile)]
                : null,
            'liked' => $this->likedCount() > self::OVERVIEW_LIKED
                ? ['label' => "All {$this->likedCount()} liked", 'href' => route('users.liked', $this->profile)]
                : null,
            'collection' => $this->count('collection') > self::OVERVIEW_COLLECTION
                ? ['label' => "All {$this->count('collection')} cards", 'href' => route('users.collection', $this->profile)]
                : null,
            'wishlist' => $this->count('wishlist') > self::OVERVIEW_COLLECTION
                ? ['label' => "All {$this->count('wishlist')} cards", 'href' => route('users.wishlist', $this->profile)]
                : null,
            'comments' => $this->commentCount() > self::OVERVIEW_COMMENTS
                ? ['label' => 'Show older comments ('.($this->commentCount() - self::OVERVIEW_COMMENTS).')', 'href' => route('users.comments', $this->profile)]
                : null,
        };
    }

    /** The decks and liked pages' query: the user's public decks, or the public decks they liked. */
    public function deckQuery(): ?PublicDeckQuery
    {
        return $this->deckQuery ??= match ($this->list) {
            'decks' => $this->baseQuery->forUser($this->profile),
            'liked' => $this->baseQuery->likedBy($this->profile),
            default => null,
        };
    }

    public function decks(): ?LengthAwarePaginator
    {
        return $this->decks ??= $this->deckQuery()?->paginate(self::DECKS_PER_PAGE);
    }

    /** @return array{q: ?string, sort: string, game: ?string, format: ?string, liked: bool} */
    public function filters(): array
    {
        return $this->deckQuery()?->filters() ?? $this->baseQuery->filters();
    }

    public function trending(): bool
    {
        return $this->filters()['sort'] === 'trending';
    }

    /** The comments on this profile, newest first, 20 per page; page links land on #comments. */
    public function comments(): LengthAwarePaginator
    {
        return $this->comments ??= $this->profile->profileComments()
            ->newest()
            ->paginate(self::COMMENTS_PER_PAGE)
            ->withQueryString()
            ->fragment('comments');
    }

    /** What was typed in the comment form before a failed post. */
    public function commentDraft(): CommentDraft
    {
        return $this->commentDraft;
    }

    private function listOverview(string $list): Collection
    {
        return $this->profile->collectionItems()
            ->cardTiles($list)
            ->selectRaw('MAX(collection_items.updated_at) as touched')
            ->orderByDesc('touched')
            ->limit(self::OVERVIEW_COLLECTION)
            ->get()
            ->each(function ($row) {
                $row->copies_label = CollectionPage::copiesLabel((int) $row->copies);
                $row->label = "{$row->name}, {$row->copies_label}";
            });
    }

    private function count(string $list): int
    {
        return $this->counts[$list] ??= match ($list) {
            'decks' => $this->profile->decks()->public()->count(),
            'liked' => Deck::public()
                ->whereHas('likes', fn ($likes) => $likes->where('user_id', $this->profile->id))
                ->count(),
            'collection' => $this->profile->collectionItems()->owned()->distinct()->count('card_key'),
            'wishlist' => $this->profile->collectionItems()->wanted()->distinct()->count('card_key'),
            'comments' => $this->profile->profileComments()->count(),
        };
    }
}
