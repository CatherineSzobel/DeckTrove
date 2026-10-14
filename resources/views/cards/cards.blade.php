{{--
    The card database. Search, filters and the view are a GET form and links (App\Cards\CardQuery builds the URLs),
    so every state has its own URL and the page works without JS. resources/js/card-database.js (cardDatabase) loads
    results in place and appends the next page when "Load more cards" scrolls into view.
--}}

<x-layout :title="$seriesLabel.' cards'" :game-switch="false" :js="['resources/js/card-database.js']">
    <div data-card-database x-data="cardDatabase" @scroll.window.passive="hideOnScroll()" class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-col gap-2">
                <p class="eyebrow">Card database</p>
                <h1 class="heading-display text-[clamp(40px,5vw,60px)] text-ink">{{ $seriesLabel }} cards</h1>
            </div>
            <x-game-switch :series="$series" />
        </div>

        <div class="search-bar">
            <section aria-label="Search and filters" class="rounded-[18px] border border-line bg-surface p-4">
                <form method="GET" action="{{ route('cards.index', $series) }}" role="search" x-ref="form" @submit.prevent="apply()" class="flex flex-col gap-4">
                    @foreach ($query->hiddenFields() as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex min-w-0 flex-[1_1_360px] gap-2">
                            <div class="relative min-w-0 flex-1">
                                <label for="card-search" class="sr-only">Search cards</label>
                                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-muted" aria-hidden="true" />
                                <input id="card-search" type="search" name="search" value="{{ $query->search() }}" maxlength="100"
                                    placeholder="Search {{ $seriesLabel }} cards" class="input w-full pl-11">
                            </div>
                            <button type="submit" class="btn-primary">Search</button>
                        </div>

                        <nav aria-label="Card view" x-ref="views" class="inline-flex gap-1 rounded-xl border border-line bg-canvas p-1">
                            @foreach ($query->viewLinks() as $link)
                            <a href="{{ $link['href'] }}" data-view="{{ $link['key'] }}" data-label="{{ $link['label'] }}" @if ($link['current']) aria-current="page" @endif class="segment">{{ $link['label'] }}</a>
                            @endforeach
                        </nav>

                        <button type="button" @click="showFilters = !showFilters" aria-controls="filters" :aria-expanded="showFilters"
                            :data-active="activeCount > 0" @if ($query->activeCount()) data-active @endif class="filter-toggle js-only">
                            <x-heroicon-o-funnel class="h-5 w-5" aria-hidden="true" />
                            Filters
                            <span class="filter-count" x-show="activeCount" x-text="activeCount" @unless ($query->activeCount()) style="display: none" @endunless>{{ $query->activeCount() }}</span>
                        </button>
                    </div>

                    <div id="filters" x-ref="filters" @unless ($query->activeCount()) data-collapsed="true" @endunless :data-collapsed="!showFilters"
                        class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-3 border-t border-line pt-4">
                        @foreach ($filterChoices as $key => $filter)
                        <div class="flex min-w-0 flex-col gap-1.5">
                            <label for="filter-{{ $key }}" class="font-mono text-xs uppercase tracking-[0.06em] text-muted">{{ $filter['label'] }}</label>
                            <select id="filter-{{ $key }}" name="{{ $key }}" @change="apply()" class="input min-h-11 py-0">
                                <option value="">All</option>
                                @foreach ($filter['choices'] as $value => $label)
                                <option value="{{ $value }}" @selected($query->isSelected($key, $value))>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach
                        <div class="flex items-end">
                            <button type="submit" class="btn-secondary no-js-only">Apply filters</button>
                        </div>
                    </div>
                </form>
            </section>
        </div>

        <div id="card-results" class="results-region" x-ref="region" @click="onRegionClick($event)" :aria-busy="loading">
            @include('cards.partials.results')
        </div>

        <p x-show="loadingMore" x-cloak aria-live="polite" class="flex items-center justify-center gap-3 text-sm text-muted">
            <span class="spinner" aria-hidden="true"></span>
            Loading more cards…
        </p>

        <x-deck.card-preview />
    </div>
</x-layout>
