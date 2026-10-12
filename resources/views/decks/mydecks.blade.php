{{--
    The signed-in user's decks: stats, then all decks as owner cards (x-deck.card variant "owner"). Everything fits on
    one page, so search and the series switch filter the cards in place with Alpine.
--}}
@php
    $builderSeries = session('series', array_key_first(config('series')));
    $publicDecks = $decks->where('is_public', true);
    $likesReceived = (int) $publicDecks->sum('likes_count');
    // What the in-place filter looks at, per card in the grid's order.
    $filterData = $decks->values()->map(fn ($deck) => [
        'game' => $deck->game,
        'text' => Str::lower($deck->name.' '.$deck->description),
    ]);
@endphp

<x-layout title="My decks">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-6 pb-2">
            <div class="flex max-w-[40rem] flex-col gap-3">
                <p class="eyebrow">Your collection</p>
                <h1 class="heading-display text-[clamp(44px,6vw,72px)] text-ink">My decks</h1>
                <p class="text-[17px] leading-relaxed text-body">Build, tune and share your decks.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('users.show', auth()->user()) }}" class="btn-secondary">
                    <x-heroicon-o-user-circle class="h-5 w-5" aria-hidden="true" />
                    Public profile
                </a>
                <a href="{{ route('decks.builder', $builderSeries) }}" class="btn-primary">
                    <x-heroicon-o-plus class="h-5 w-5" aria-hidden="true" />
                    New deck
                </a>
            </div>
        </div>

        <div class="flex flex-wrap gap-2.5">
            <span class="inline-flex items-baseline gap-2 rounded-[10px] border border-line bg-surface px-3 py-2 text-[13px] text-body">
                <b class="font-mono text-base text-accent-ink">{{ $decks->count() }}</b>
                {{ Str::plural('deck', $decks->count()) }}
            </span>
            @foreach (config('series') as $key => $config)
            <span class="inline-flex items-baseline gap-2 rounded-[10px] border border-line bg-surface px-3 py-2 text-[13px] text-body">
                <b class="font-mono text-base text-ink">{{ $decks->where('game', $key)->count() }}</b>
                {{ $config['label'] }}
            </span>
            @endforeach
            <span class="inline-flex items-baseline gap-2 rounded-[10px] border border-line bg-surface px-3 py-2 text-[13px] text-body">
                <b class="font-mono text-base text-ink">{{ $publicDecks->count() }}</b>
                public
            </span>
            <span class="inline-flex items-baseline gap-2 rounded-[10px] border border-line bg-surface px-3 py-2 text-[13px] text-body">
                <b class="font-mono text-base text-ink">{{ $likesReceived }}</b>
                {{ Str::plural('like', $likesReceived) }} received
            </span>
        </div>

        @if ($decks->isEmpty())
        <x-empty-state title="You don’t have any decks yet">
            Start one in the deck builder, or copy a public deck to make it your own.
            <x-slot:actions>
                <a href="{{ route('decks.builder', $builderSeries) }}" class="btn-primary">Create your first deck</a>
                <a href="{{ route('public-deck') }}" class="btn-secondary">Browse public decks</a>
            </x-slot:actions>
        </x-empty-state>
        @else
        <div x-data="deckSearch(@js($filterData))" class="flex flex-col gap-6">
            <div class="flex flex-wrap items-center gap-3 rounded-[18px] border border-line bg-surface p-4">
                <div class="relative min-w-0 flex-[1_1_320px]">
                    <label for="my-deck-search" class="sr-only">Search your decks</label>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-muted" aria-hidden="true" />
                    <input id="my-deck-search" type="search" x-model="q" placeholder="Search your decks by name or description" class="input w-full pl-11">
                </div>

                <div role="group" aria-label="Series" class="inline-flex flex-wrap gap-1 rounded-xl border border-line bg-canvas p-1">
                    <button type="button" @click="series = 'all'" :aria-pressed="(series === 'all').toString()" aria-pressed="true"
                        :class="series === 'all' ? 'bg-surface-2 font-bold text-ink' : 'font-medium text-muted hover:text-ink'"
                        class="focus-ring min-h-10 rounded-[9px] px-3.5 text-sm">All</button>
                    @foreach (config('series') as $key => $config)
                    <button type="button" @click="series = @js($key)" :aria-pressed="(series === @js($key)).toString()" aria-pressed="false"
                        :class="series === @js($key) ? 'bg-surface-2 font-bold text-ink' : 'font-medium text-muted hover:text-ink'"
                        class="focus-ring min-h-10 rounded-[9px] px-3.5 text-sm">{{ $config['label'] }}</button>
                    @endforeach
                </div>
            </div>

            <p class="text-sm text-muted" aria-live="polite">
                <span class="font-mono font-bold text-ink" x-text="shown + (shown === 1 ? ' deck' : ' decks')">{{ $decks->count() }} {{ Str::plural('deck', $decks->count()) }}</span>
                · newest first
            </p>

            <div class="grid grid-cols-[repeat(auto-fill,minmax(min(300px,100%),1fr))] gap-6">
                @foreach ($decks->values() as $deck)
                <x-deck.card :deck="$deck" variant="owner" x-show="matches({{ $loop->index }})" />
                @endforeach
            </div>

            <x-empty-state x-cloak x-show="shown === 0" title="No decks match">Try another search or series.</x-empty-state>
        </div>
        @endif
    </div>
</x-layout>
