{{-- Card search column of the deck builder: search, filters and the results (infinite scroll). --}}
<aside aria-label="Card search" class="flex min-w-0 flex-[1_1_380px] flex-col gap-4 rounded-[18px] border border-line bg-surface p-5 lg:sticky lg:top-6">
    <h2 class="heading-display text-[26px] text-ink">Card search</h2>

    <div class="flex gap-2">
        <div class="relative flex-1">
            <label for="cardSearch" class="sr-only">Search cards</label>
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-muted" aria-hidden="true" />
            <input id="cardSearch" type="search" x-model="search" @keydown.enter.prevent="runSearch()"
                placeholder="Search cards and press Enter" class="input w-full pl-11">
        </div>
        <button type="button" @click="showFilters = !showFilters" :aria-expanded="showFilters" aria-controls="filterPanel"
            class="btn-secondary px-3.5" :class="showFilters && 'bg-surface-2'">
            <x-heroicon-o-funnel class="h-5 w-5" aria-hidden="true" />
            Filters
        </button>
    </div>

    <div id="filterPanel" x-show="showFilters" x-cloak role="group" aria-label="Filters"
        class="grid grid-cols-2 gap-2.5 rounded-xl border border-line bg-canvas p-3.5">
        @foreach ($filterChoices as $key => $filter)
        <div class="flex min-w-0 flex-col gap-1">
            <label for="filter-{{ $key }}" class="font-mono text-[11px] uppercase tracking-[0.08em] text-muted">{{ $filter['label'] }}</label>
            <select id="filter-{{ $key }}" x-model="filters.{{ $key }}" @change="runSearch()"
                class="focus-ring h-11 min-w-0 rounded-[10px] border-[1.5px] border-line-strong bg-surface px-2.5 text-sm text-ink">
                <option value="">All</option>
                @foreach ($filter['choices'] as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between gap-2 text-[13px]">
        <span aria-live="polite" class="font-mono text-muted" x-text="resultSummary">@include('cards.partials.result-count')</span>
        <button type="button" @click="clearSearch()" class="focus-ring py-2 font-bold text-accent-ink">Clear</button>
    </div>

    <div x-ref="results" @scroll.passive="hidePreview(); onResultsScroll($event)"
        class="grid max-h-[760px] grid-cols-3 content-start gap-3.5 overflow-y-auto p-1">
        @include('decks.partials.cards-view')
    </div>
    <p x-show="loading" x-cloak aria-live="polite" class="text-center text-sm text-muted">Loading more cards…</p>
</aside>
