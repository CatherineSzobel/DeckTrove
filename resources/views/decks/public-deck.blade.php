{{--
    Public decks: search, series, format, sort and "Liked by me" are plain links and a GET form (see PublicDeckQuery for
    what the server accepts), so every state has its own URL and the page works without JS.
--}}

<x-layout title="Public decks">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-6 pb-2">
            <div class="flex max-w-[40rem] flex-col gap-3">
                <p class="eyebrow">Community</p>
                <h1 class="heading-display text-[clamp(44px,6vw,72px)] text-ink">Public decks</h1>
                <p class="text-[17px] leading-relaxed text-body">Browse decks shared by the community and get inspired.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <nav aria-label="Series" class="inline-flex flex-wrap gap-1 rounded-xl border border-line bg-surface p-1">
                    <a href="{{ $query->url(['game' => null, 'format' => null]) }}" data-game="all" @if (! $filters['game']) aria-current="true" @endif class="segment">All series</a>
                    @foreach (config('series') as $key => $config)
                    <a href="{{ $query->url(['game' => $key, 'format' => null]) }}" data-game="{{ $key }}" @if ($filters['game'] === $key) aria-current="true" @endif class="segment">{{ $config['label'] }}</a>
                    @endforeach
                </nav>

                @auth
                <a href="{{ route('decks.builder', session('series', array_key_first(config('series')))) }}" class="btn-primary">
                    <x-heroicon-o-plus class="h-5 w-5" aria-hidden="true" />
                    New deck
                </a>
                @endauth
            </div>
        </div>

        <form method="GET" action="{{ route('public-deck') }}" role="search"
            class="flex flex-wrap items-center gap-3 rounded-[18px] border border-line bg-surface p-4">
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            @if ($filters['game'])
            <input type="hidden" name="game" value="{{ $filters['game'] }}">
            @endif
            @if ($filters['liked'])
            <input type="hidden" name="liked" value="1">
            @endif

            <div class="relative min-w-0 flex-[1_1_320px]">
                <label for="deck-search" class="sr-only">Search public decks</label>
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-muted" aria-hidden="true" />
                <input id="deck-search" type="search" name="q" value="{{ $filters['q'] }}" maxlength="100"
                    placeholder="Search deck names, descriptions or players" class="input w-full pl-11">
                <button type="submit" class="sr-only">Search</button>
            </div>

            <nav aria-label="Sort decks" class="inline-flex gap-1 rounded-xl border border-line bg-canvas p-1 max-sm:w-full">
                @foreach ($sorts as $key => $label)
                <a href="{{ $query->url(['sort' => $key]) }}" data-sort="{{ $key }}" @if ($filters['sort'] === $key) aria-current="true" @endif
                    class="segment max-sm:flex-1">{{ $label }}</a>
                @endforeach
            </nav>

            @if ($formats)
            <div class="flex items-center gap-2.5">
                <label for="deck-format" class="font-mono text-xs uppercase tracking-[0.08em] text-muted">Format</label>
                <select id="deck-format" name="format" data-autosubmit class="input">
                    <option value="">All formats</option>
                    @foreach ($formats as $key => $label)
                    <option value="{{ $key }}" @selected($filters['format'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @auth
            <a href="{{ $query->url(['liked' => $filters['liked'] ? null : 1]) }}" @if ($filters['liked']) aria-current="true" @endif
                @class([
                    'focus-ring inline-flex h-12 items-center gap-2 rounded-xl border-[1.5px] px-5 font-bold transition',
                    'border-accent-ink bg-warn-bg text-accent-ink' => $filters['liked'],
                    'border-line-strong text-ink hover:bg-surface-2' => ! $filters['liked'],
                ])>
                @if ($filters['liked'])
                <x-heroicon-s-heart class="h-5 w-5" aria-hidden="true" />
                @else
                <x-heroicon-o-heart class="h-5 w-5" aria-hidden="true" />
                @endif
                Liked by me
            </a>
            @endauth
        </form>

        <div class="flex min-h-11 flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-muted" aria-live="polite">
                <span class="font-mono font-bold text-ink">{{ $decks->total() }} {{ Str::plural('deck', $decks->total()) }}</span> · {{ $query->sortNote() }}
            </p>
            @if ($query->isFiltering())
            <a href="{{ route('public-deck', ['sort' => $filters['sort']]) }}" class="focus-ring rounded-sm text-sm font-bold text-accent-ink">Clear filters</a>
            @endif
        </div>

        @if ($decks->isNotEmpty())
        <div id="deck-grid">
            @include('decks.partials.deck-card', ['trending' => $filters['sort'] === 'trending'])
        </div>
        @elseif ($filters['liked'] && ! $filters['q'] && ! $filters['game'])
        <x-empty-state title="You haven’t liked any decks yet">
            <x-slot:icon><x-heroicon-o-heart class="h-8 w-8 text-accent-ink" aria-hidden="true" /></x-slot:icon>
            Tap the heart on a deck to keep it here. Only you can see which decks you liked.
            <x-slot:actions><a href="{{ route('public-deck', ['sort' => 'trending']) }}" class="btn-primary">See trending decks</a></x-slot:actions>
        </x-empty-state>
        @elseif ($query->isFiltering())
        <x-empty-state title="No decks match">
            Try another search or format, or clear the filters to see every public deck.
            <x-slot:actions><a href="{{ route('public-deck', ['sort' => $filters['sort']]) }}" class="btn-secondary">Clear filters</a></x-slot:actions>
        </x-empty-state>
        @else
        <x-empty-state title="No public decks yet">
            Decks show up here once their owners make them public.
            <x-slot:actions>@auth<a href="{{ route('decks') }}" class="btn-secondary">Go to my decks</a>@endauth</x-slot:actions>
        </x-empty-state>
        @endif
    </div>
</x-layout>
