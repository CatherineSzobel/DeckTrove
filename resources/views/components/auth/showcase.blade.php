{{-- Screenshots and selling points next to the log in / create account card (App\View\Components\Auth\Showcase). Hidden on small screens. --}}
<section aria-label="About DeckTrove" class="hidden min-w-0 flex-[1_1_440px] flex-col gap-8 lg:flex">
    <div class="relative aspect-[5/3.6]">
        <div class="showcase-glow" aria-hidden="true"></div>
        <img src="{{ Vite::asset('resources/img/screenshots/card-database.png') }}" alt="DeckTrove card database" data-shot="1" class="showcase-shot">
        <img src="{{ Vite::asset('resources/img/screenshots/deck-builder.png') }}" alt="DeckTrove deck builder" data-shot="2" class="showcase-shot">
    </div>

    <ol class="flex flex-col gap-3.5">
        @foreach ($points as $point)
        <li class="flex items-center gap-3.5 text-base text-body">
            <span class="font-mono text-[13px] text-accent-ink">{{ $point['number'] }}</span>
            {{ $point['text'] }}
        </li>
        @endforeach
    </ol>
</section>
