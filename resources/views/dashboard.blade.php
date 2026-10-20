{{--
    The signed-in user's home: welcome and actions, deck counts, their newest decks (filtered by series with plain
    links), quick links and random cards with their own series switch (?cards=, which also becomes the remembered
    series). All data comes from DashboardService.
--}}

<x-layout title="Dashboard">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div class="flex min-w-0 flex-col gap-2.5">
                <p class="eyebrow">Dashboard</p>
                <h1 class="heading-display break-words text-[clamp(40px,5vw,64px)] text-ink">Welcome back, {{ $username }}</h1>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('decks.builder', $builderSeries) }}" class="btn-primary">
                    <x-heroicon-o-plus class="h-5 w-5" aria-hidden="true" />
                    New deck
                </a>
                <a href="{{ route('profile') }}" class="btn-secondary">Profile</a>
                <a href="{{ route('settings') }}" class="btn-secondary">Settings</a>
            </div>
        </div>

        <section aria-label="Quick stats" class="grid grid-cols-[repeat(auto-fit,minmax(min(200px,100%),1fr))] gap-4">
            @foreach ($stats as $stat)
            <x-stat-tile :label="$stat['label']" :value="$stat['value']" :accent="$stat['accent']" />
            @endforeach
        </section>

        <div class="flex flex-wrap items-start gap-6">
            <section aria-labelledby="your-decks" class="flex min-w-0 flex-[999_1_640px] flex-col gap-5 rounded-[20px] border border-line bg-surface p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="your-decks" class="heading-display text-[32px] text-ink">Your decks</h2>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-segment-links :links="$seriesLinks" label="Filter by series" />
                        <a href="{{ route('decks.mine') }}" class="focus-ring inline-flex min-h-11 items-center gap-1.5 rounded-sm font-bold text-accent-ink hover:underline">
                            View all
                            <x-heroicon-o-arrow-right class="h-4 w-4" aria-hidden="true" />
                        </a>
                    </div>
                </div>

                @if ($recentDecks->isEmpty())
                <x-empty-state :title="$emptyTitle" heading="h3">
                    Start one in the deck builder, or copy a public deck to make it your own.
                    <x-slot:actions>
                        <a href="{{ route('decks.builder', $builderSeries) }}" class="btn-primary">Create a deck</a>
                        <a href="{{ route('decks.index') }}" class="btn-secondary">Browse public decks</a>
                    </x-slot:actions>
                </x-empty-state>
                @else
                <div class="grid grid-cols-[repeat(auto-fill,minmax(min(220px,100%),1fr))] gap-4">
                    @foreach ($recentDecks as $deck)
                    <x-deck.tile :deck="$deck" />
                    @endforeach
                </div>
                @endif
            </section>

            <section aria-labelledby="quick-links" class="flex min-w-0 flex-[1_1_300px] flex-col gap-4 rounded-[20px] border border-line bg-surface p-6">
                <h2 id="quick-links" class="heading-display text-[32px] text-ink">Quick links</h2>
                <ul class="flex flex-col gap-2.5">
                    @foreach ($quickLinks as $link)
                    <li>
                        <a href="{{ $link['href'] }}" class="focus-ring flex min-h-14 items-center justify-between gap-3 rounded-xl border-[1.5px] border-line bg-canvas px-4 font-bold text-ink transition hover:border-accent">
                            {{ $link['title'] }}
                            <x-heroicon-o-arrow-right class="h-[18px] w-[18px] flex-none text-accent-ink" aria-hidden="true" />
                        </a>
                    </li>
                    @endforeach
                </ul>
            </section>
        </div>

        <section aria-labelledby="discover" class="flex flex-col gap-4 overflow-hidden rounded-[20px] border border-line bg-surface p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="discover" class="heading-display text-[32px] text-ink">Discover random cards</h2>
                <x-segment-links :links="$randomSeriesLinks" label="Random cards series" />
            </div>
            @if ($randomCards->isEmpty())
            <p class="text-[15px] text-muted">No random cards right now.</p>
            @else
            <ul class="flex gap-4 overflow-x-auto px-1 pb-2 pt-3">
                @foreach ($randomCards as $card)
                <li class="flex-none">
                    <a href="{{ $card->link() }}" class="random-card">
                        <img src="{{ $card->imageSmall() }}" alt="{{ $card->name() }}" loading="lazy" class="h-full w-full object-cover">
                    </a>
                </li>
                @endforeach
            </ul>
            @endif
        </section>
    </div>
</x-layout>
