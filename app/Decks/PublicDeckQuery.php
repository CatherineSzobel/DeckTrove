<?php

namespace App\Decks;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * Public decks as listed on the public deck page and on profiles: search, game and format filters, the viewer's
 * liked decks, and three sort orders. Request parameters are normalized here; invalid values fall back to the
 * defaults instead of failing.
 */
class PublicDeckQuery
{
    public const SORTS = ['newest' => 'Newest', 'liked' => 'Most liked', 'trending' => 'Trending'];

    /** How each sort reads after the result count, e.g. "12 decks · most liked first". */
    public const SORT_NOTES = ['newest' => 'newest first', 'liked' => 'most liked first', 'trending' => 'most likes this week first'];

    private const SEARCH_LENGTH = 100;

    private ?User $owner = null;

    private ?User $liker = null;

    private function __construct(
        private ?User $viewer,
        private ?string $search,
        private string $sort,
        private ?string $game,
        private ?string $format,
        private bool $liked,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $series = config('series');
        $viewer = $request->user();

        $game = self::string($request, 'game');
        $game = array_key_exists((string) $game, $series) ? $game : null;

        // A format only means something within one game. `casual` is a deck without a format.
        $format = self::string($request, 'format');
        $formats = $game ? array_keys($series[$game]['deck']['formats'] ?? []) : [];
        $format = $game && ($format === 'casual' || in_array($format, $formats, true)) ? $format : null;

        $sort = self::string($request, 'sort');
        $sort = array_key_exists((string) $sort, self::SORTS) ? $sort : 'newest';

        $search = mb_substr(trim((string) self::string($request, 'q')), 0, self::SEARCH_LENGTH);

        return new self(
            $viewer,
            $search === '' ? null : $search,
            $sort,
            $game,
            $format,
            $viewer !== null && $request->boolean('liked'),
        );
    }

    /**
     * Newest first, every series, no search: for short lists that take nothing from the URL (the profile overview).
     */
    public static function defaults(?User $viewer): self
    {
        return new self($viewer, null, 'newest', null, null, false);
    }

    /** The first $limit decks, for short lists without paging. */
    public function take(int $limit): Collection
    {
        return $this->query()->limit($limit)->get();
    }

    /**
     * One owner's public decks, for their profile. Only the sort and the game apply there.
     */
    public function forUser(User $owner): self
    {
        $query = clone $this;
        $query->owner = $owner;
        $query->search = null;
        $query->format = null;
        $query->liked = false;

        return $query;
    }

    /**
     * The public decks a user liked, for the Liked tab on their profile. Only the sort and the game apply there.
     */
    public function likedBy(User $liker): self
    {
        $query = clone $this;
        $query->liker = $liker;
        $query->search = null;
        $query->format = null;
        $query->liked = false;

        return $query;
    }

    /**
     * The filters actually applied, so the page can preselect its controls.
     *
     * @return array{q: ?string, sort: string, game: ?string, format: ?string, liked: bool}
     */
    public function filters(): array
    {
        return [
            'q' => $this->search,
            'sort' => $this->sort,
            'game' => $this->game,
            'format' => $this->format,
            'liked' => $this->liked,
        ];
    }

    /**
     * The applied filters as query parameters, with some changed: links for the page's controls, e.g.
     * route('public-deck', $query->queryWith(['sort' => 'liked'])). Null removes a filter; `page` is never kept, so
     * any change starts again at page one.
     *
     * @return array<string, string|int>
     */
    public function queryWith(array $changes = []): array
    {
        $current = [
            'q' => $this->search,
            'sort' => $this->sort,
            'game' => $this->game,
            'format' => $this->format,
            'liked' => $this->liked ? 1 : null,
        ];

        return array_filter(array_merge($current, $changes), fn ($value) => $value !== null);
    }

    /**
     * A link to this list with some filters changed: the public deck page, the owner's decks page for forUser(), or
     * the liked page for likedBy().
     */
    public function url(array $changes = []): string
    {
        return match (true) {
            $this->liker !== null => route('users.liked', [$this->liker, ...$this->queryWith($changes)]),
            $this->owner !== null => route('users.decks', [$this->owner, ...$this->queryWith($changes)]),
            default => route('public-deck', $this->queryWith($changes)),
        };
    }

    /**
     * Whether anything narrows the list (search, series, format or liked); the sort doesn't count.
     */
    public function isFiltering(): bool
    {
        return $this->search !== null || $this->game !== null || $this->format !== null || $this->liked;
    }

    public function sortNote(): string
    {
        return self::SORT_NOTES[$this->sort];
    }

    /**
     * The selected game's formats for the format select, Casual first. Empty when no game is selected.
     *
     * @return array<string, string> format key => label
     */
    public function formatOptions(): array
    {
        if (! $this->game) {
            return [];
        }

        return ['casual' => 'Casual'] + collect(config("series.{$this->game}.deck.formats", []))
            ->map(fn (array $format) => $format['label'])
            ->all();
    }

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage)->withQueryString();
    }

    private function query(): Builder
    {
        $query = Deck::public()
            ->with('user')
            ->withCardCount()
            ->withCount('likes')
            ->when($this->viewer, fn (Builder $query, User $viewer) => $query->withExists([
                'likes as liked_by_viewer' => fn (Builder $likes) => $likes->where('user_id', $viewer->id),
            ]))
            ->when($this->owner, fn (Builder $query, User $owner) => $query->where('user_id', $owner->id))
            ->when($this->liker, fn (Builder $query, User $liker) => $query->whereHas(
                'likes', fn (Builder $likes) => $likes->where('user_id', $liker->id)
            ))
            ->when($this->search, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('name', "%{$search}%", caseSensitive: false)
                ->orWhereLike('description', "%{$search}%", caseSensitive: false)
                ->orWhereHas('user', fn (Builder $user) => $user->whereLike('username', "%{$search}%", caseSensitive: false))))
            ->when($this->game, fn (Builder $query, string $game) => $query->where('game', $game))
            ->when($this->format, fn (Builder $query, string $format) => $format === 'casual'
                ? $query->whereNull('format')
                : $query->where('format', $format))
            ->when($this->liked, fn (Builder $query) => $query->whereHas(
                'likes', fn (Builder $likes) => $likes->where('user_id', $this->viewer->id)
            ));

        match ($this->sort) {
            'liked' => $query->orderByDesc('likes_count'),
            'trending' => $query
                ->withCount(['likes as recent_likes_count' => fn (Builder $likes) => $likes->where('created_at', '>=', now()->subWeek())])
                ->orderByDesc('recent_likes_count')
                ->orderByDesc('likes_count'),
            default => null,
        };

        // Newest breaks ties, then the id, so pages stay stable when values are equal.
        return $query->latest()->orderByDesc('id');
    }

    /**
     * A query parameter as a string, or null when it's missing or not a string (e.g. `game[]=...`).
     */
    private static function string(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : null;
    }
}
