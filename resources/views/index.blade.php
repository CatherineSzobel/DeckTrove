{{-- The home page (App\Home\HomePage): hero, the games, three steps and a call to action. Full-width sections. --}}

<x-layout :contained="false">
    <section aria-labelledby="hero-title" class="mx-auto flex max-w-7xl flex-wrap items-center gap-14 px-4 py-12 sm:px-6 lg:py-[88px]">
        <div class="flex min-w-0 flex-[1_1_440px] flex-col gap-6">
            <p class="eyebrow">{{ $home->eyebrow() }}</p>
            <h1 id="hero-title" class="heading-display text-[clamp(52px,7vw,96px)] text-ink">Every card.<br>Every deck.<br><span class="text-accent-ink">One trove.</span></h1>
            <p class="max-w-[34rem] text-lg leading-relaxed text-body">Search full card databases, build decks that are checked against each game’s rules, and share them with the community.</p>
            <div class="flex flex-wrap gap-3">
                <a href="#games" class="btn-primary">
                    Browse cards
                    <x-heroicon-o-arrow-right class="h-5 w-5" aria-hidden="true" />
                </a>
                <a href="{{ $home->builderUrl() }}" class="btn-secondary">Open the deck builder</a>
            </div>
            @if ($home->showGuestNote())
            <p class="text-sm text-muted">Browsing cards is open to everyone — sign in to build and save decks.</p>
            @endif
        </div>

        <x-showcase-shots class="min-w-0 flex-[1_1_440px]"
            :alts="['DeckTrove card database showing Yu-Gi-Oh! search results', 'DeckTrove deck builder with main, extra and side deck zones']" />
    </section>

    <section id="games" aria-labelledby="games-title" class="scroll-mt-6 border-y border-line bg-surface">
        <div class="mx-auto flex max-w-7xl flex-col gap-10 px-4 py-16 sm:px-6 lg:py-24">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="flex flex-col gap-2">
                    <p class="eyebrow">Draw a card</p>
                    <h2 id="games-title" class="heading-display text-[clamp(40px,5vw,64px)] text-ink">Pick your game</h2>
                </div>
                <p class="max-w-[26rem] text-body">Start in the card database, then take what you find straight into the deck builder.</p>
            </div>

            <div class="grid grid-cols-[repeat(auto-fill,minmax(min(220px,100%),1fr))] gap-6">
                @foreach ($home->games() as $game)
                @if ($game['live'])
                <a href="{{ $game['href'] }}" class="game-tile">
                    @include('partials.game-tile', ['game' => $game])
                </a>
                @else
                <div aria-disabled="true" class="game-tile">
                    @include('partials.game-tile', ['game' => $game])
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </section>

    <section aria-labelledby="steps-title" class="mx-auto flex max-w-7xl flex-col gap-12 px-4 py-16 sm:px-6 lg:py-24">
        <h2 id="steps-title" class="heading-display text-[clamp(36px,4vw,52px)] text-ink">From first search to finished deck</h2>
        <ol class="grid grid-cols-[repeat(auto-fit,minmax(min(260px,100%),1fr))] gap-8">
            @foreach ($home->steps() as $step)
            <li class="flex flex-col gap-3 border-t border-line pt-6">
                <span class="font-mono text-[13px] text-accent-ink">{{ $step['number'] }}</span>
                <h3 class="heading-display text-[28px] text-ink">{{ $step['title'] }}</h3>
                <p class="text-base leading-relaxed text-body">{{ $step['text'] }}</p>
            </li>
            @endforeach
        </ol>
    </section>

    <div class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:pb-24">
        <section aria-labelledby="cta-title" class="flex flex-wrap items-center justify-between gap-8 rounded-3xl border border-line bg-surface p-8 sm:p-12">
            <div class="flex items-center gap-5">
                <img src="{{ Vite::asset('resources/img/decktrove-logo.png') }}" alt="" class="h-16 w-16 shrink-0 object-contain">
                <div class="flex flex-col gap-2">
                    <h2 id="cta-title" class="heading-display text-[40px] text-ink">{{ $home->cta()['title'] }}</h2>
                    <p class="text-body">{{ $home->cta()['text'] }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ $home->cta()['primary']['href'] }}" class="btn-primary">{{ $home->cta()['primary']['label'] }}</a>
                <a href="{{ $home->cta()['secondary']['href'] }}" class="btn-secondary">{{ $home->cta()['secondary']['label'] }}</a>
            </div>
        </section>
    </div>
</x-layout>
