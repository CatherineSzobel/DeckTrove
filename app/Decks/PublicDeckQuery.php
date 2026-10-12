<?php

namespace App\Decks;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Public decks as listed on the public deck page and on profiles: search, game and format filters, the viewer's
 * liked decks, and three sort orders. Request parameters are normalized here; invalid values fall back to the
 * defaults instead of failing.
 */
class PublicDeckQuery
{
    public const SORTS = ['newest' => 'Newest', 'liked' => 'Most liked', 'trending' => 'Trending'];

    private const SEARCH_LENGTH = 100;

    private ?User $owner = null;

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
