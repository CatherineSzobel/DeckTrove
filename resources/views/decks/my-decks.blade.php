{{--
    The signed-in user's decks: stats, then all decks as owner cards (x-deck.card variant "owner"). Everything fits on
    one page, so search and the series switch filter the cards in place with Alpine.
--}}

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
            <x-stat-chip :value="$stats['decks']" :label="Str::plural('deck', $stats['decks'])" accent />
            @foreach ($stats['series'] as $series => $count)
            <x-stat-chip :value="$count" :label="config('series.'.$series.'.label')" />
            @endforeach
            <x-stat-chip :value="$stats['public']" label="public" />
            <x-stat-chip :value="$stats['likes_received']" :label="Str::plural('like', $stats['likes_received']).' received'" />
        </div>

        @if ($decks->isEmpty())
        <x-empty-state title="You don’t have any decks yet">
            Start one in the deck builder, or copy a public deck to make it your own.
            <x-slot:actions>
                <a href="{{ route('decks.builder', $builderSeries) }}" class="btn-primary">Create your first deck</a>
                <a href="{{ route('decks.index') }}" class="btn-secondary">Browse public decks</a>
            </x-slot:actions>
        </x-empty-state>
        @else
        <div x-data="deckSearch(@js($searchData))" class="flex flex-col gap-6">
            <div class="flex flex-wrap items-center gap-3 rounded-[18px] border border-line bg-surface p-4">
                <div class="relative min-w-0 flex-[1_1_320px]">
                    <label for="my-deck-search" class="sr-only">Search your decks</label>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-muted" aria-hidden="true" />
                    <input id="my-deck-search" type="search" x-model="q" placeholder="Search your decks by name or description" class="input w-full pl-11">
                </div>

                <div role="group" aria-label="Series" class="inline-flex flex-wrap gap-1 rounded-xl border border-line bg-canvas p-1">
                    <button type="button" @click="series = 'all'" aria-pressed="true" :aria-pressed="pressed('all')" class="segment">All</button>
                    @foreach (config('series') as $key => $config)
                    <button type="button" @click="series = @js($key)" aria-pressed="false" :aria-pressed="pressed(@js($key))" class="segment">{{ $config['label'] }}</button>
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
