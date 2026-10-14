<?php

namespace App\Cards;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * The card database's search, filters, view and page, read from the request, and every link the page needs (view
 * switch, filter chips, Clear all, next page). Like App\Decks\PublicDeckQuery, invalid values fall back instead of
 * failing, and links never keep `page`, so any change starts again at page one.
 */
final class CardQuery
{
    /** The result views, in the order the switch shows them. */
    public const VIEWS = ['full' => 'Full', 'images' => 'Images', 'list' => 'List'];

    public const DEFAULT_VIEW = 'full';

    private const SEARCH_LENGTH = 100;

    /**
     * @param  array<string, string>  $filters  the applied filters, in config order
     * @param  array<string, array{label: string, choices: array<string, string>}>  $filterChoices  from FilterChoices::for()
     */
    private function __construct(
        private string $series,
        private string $view,
        private ?string $search,
        private array $filters,
        private int $page,
        private array $filterChoices,
    ) {}

    public static function fromRequest(Request $request, string $series, array $filterChoices): self
    {
        $view = self::string($request, 'view');
        $search = mb_substr(trim((string) self::string($request, 'search')), 0, self::SEARCH_LENGTH);

        $filters = collect(array_keys(config("series.$series.filters", [])))
            ->mapWithKeys(fn (string $key) => [$key => trim((string) self::string($request, $key))])
            ->reject(fn (string $value) => $value === '')
            ->all();

        return new self(
            $series,
            array_key_exists((string) $view, self::VIEWS) ? $view : self::DEFAULT_VIEW,
            $search === '' ? null : $search,
            $filters,
            max((int) self::string($request, 'page'), 1),
            $filterChoices,
        );
    }

    /**
     * What the series' CardProvider::search() gets.
     */
    public function params(): array
    {
        return array_filter(
            [...$this->filters, 'search' => $this->search, 'view' => $this->view, 'page' => $this->page],
            fn ($value) => $value !== null,
        );
    }

    public function view(): string
    {
        return $this->view;
    }

    public function search(): ?string
    {
        return $this->search;
    }

    /**
     * @return array<string, string>
     */
    public function filters(): array
    {
        return $this->filters;
    }

    public function isSelected(string $key, int|string $value): bool
    {
        return ($this->filters[$key] ?? null) === (string) $value;
    }

    /**
     * Hidden fields for the search form, so a search keeps the view.
     *
     * @return array<string, string>
     */
    public function hiddenFields(): array
    {
        return $this->view === self::DEFAULT_VIEW ? [] : ['view' => $this->view];
    }

    /**
     * The card database with some parameters changed. Null removes one.
     */
    public function url(array $changes = []): string
    {
        return route('cards.index', [$this->series, ...$this->queryWith($changes)]);
    }

    public function pageUrl(int $page): string
    {
        return route('cards.index', [$this->series, ...$this->queryWith(), 'page' => $page]);
    }

    public function nextPageUrl(LengthAwarePaginator $cards): ?string
    {
        return $cards->hasMorePages() ? $this->pageUrl($cards->currentPage() + 1) : null;
    }

    /**
     * @return list<array{key: string, label: string, href: string, current: bool}>
     */
    public function viewLinks(): array
    {
        return collect(self::VIEWS)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'href' => $this->url(['view' => $key]),
                'current' => $key === $this->view,
            ])
            ->values()
            ->all();
    }

    /**
     * One chip per applied filter, e.g. "Color: Red", with a link to the same page without that filter.
     *
     * @return list<array{key: string, filterLabel: string, label: string, removeUrl: string}>
     */
    public function chips(): array
    {
        return collect($this->filters)
            ->map(function (string $value, string $key) {
                $filter = $this->filterChoices[$key] ?? ['label' => ucfirst($key), 'choices' => []];

                return [
                    'key' => $key,
                    'filterLabel' => $filter['label'],
                    'label' => $filter['label'].': '.($filter['choices'][$value] ?? $value),
                    'removeUrl' => $this->url([$key => null]),
                ];
            })
            ->values()
            ->all();
    }

    public function activeCount(): int
    {
        return count($this->filters);
    }

    /**
     * Whether a search or a filter narrows the results.
     */
    public function isFiltering(): bool
    {
        return $this->search !== null || $this->filters !== [];
    }

    /**
     * The card database without search and filters, in the same view.
     */
    public function clearUrl(): string
    {
        return $this->url(['search' => null, ...array_fill_keys(array_keys($this->filters), null)]);
    }

    /**
     * The current parameters with some changed, as a route query. The default view is left out.
     */
    private function queryWith(array $changes = []): array
    {
        $query = array_merge(['view' => $this->view, 'search' => $this->search, ...$this->filters], $changes);

        if (($query['view'] ?? null) === self::DEFAULT_VIEW) {
            $query['view'] = null;
        }

        return array_filter($query, fn ($value) => $value !== null);
    }

    /**
     * A query parameter as a string, or null when it's missing or not a string (e.g. `race[]=...`).
     */
    private static function string(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : null;
    }
}
