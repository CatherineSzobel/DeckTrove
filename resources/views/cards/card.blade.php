{{-- A card's page: the image (both faces for double-faced cards), type, stats, text, printings and related cards. --}}

<x-layout :title="$card->name()" :game-switch="false" :js="['resources/js/card-page.js']">
    <div class="mx-auto flex max-w-7xl flex-col gap-10 px-4 py-8 sm:px-6">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-muted">
            <a href="{{ route('cards.index', $series) }}" class="focus-ring rounded-sm text-body hover:text-ink">{{ $seriesLabel }} cards</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-ink">{{ $card->name() }}</span>
        </nav>

        <div class="flex flex-wrap items-start gap-14">
            <div class="card-glow w-full max-w-[400px] flex-[1_1_320px]" @if ($card->hasFaces()) x-data="cardFaces(@js($card->faceImages()))" @endif>
                <img src="{{ $card->image() }}" @if ($card->hasFaces()) :src="current" @endif alt="{{ $card->name() }}"
                    class="relative aspect-[59/86] w-full rounded-[14px] object-cover shadow-[0_30px_60px_rgba(0,0,0,0.45)]">
                @if ($card->hasFaces())
                <button type="button" @click="next()" x-text="label" class="js-only btn-secondary relative mt-4 w-full">Show other face</button>
                @endif
            </div>

            <div class="flex min-w-0 flex-[999_1_480px] flex-col gap-7">
                <div class="flex flex-col gap-3">
                    @if ($card->typeLine())
                    <p class="eyebrow">{{ $card->typeLine() }}</p>
                    @endif
                    <h1 class="heading-display text-[clamp(44px,5vw,64px)] text-ink">{{ $card->name() }}</h1>
                </div>

                <dl class="flex flex-wrap gap-3">
                    @foreach ($card->detailStats() as $stat)
                    <x-stat-box :label="$stat['label']" :value="$stat['value']" />
                    @endforeach
                </dl>

                @if ($card->description())
                <div class="whitespace-pre-line rounded-2xl border border-line bg-surface px-6 py-5 text-[17px] leading-[1.7] text-body">{{ $card->description() }}</div>
                @endif

                <section aria-labelledby="printings" class="flex flex-col gap-3">
                    <h2 id="printings" class="heading-display text-[28px] text-ink">Printings</h2>
                    <x-print-list :packs="$card->printPacks($series)" />
                </section>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('decks.builder', [$series, 'search' => $card->name()]) }}" class="btn-primary">
                        <x-heroicon-o-plus class="h-5 w-5" aria-hidden="true" />
                        Open in deck builder
                    </a>
                    <a href="{{ route('cards.index', $series) }}" class="btn-secondary">Back to the database</a>
                </div>
            </div>
        </div>

        @if ($setCards->isNotEmpty())
        <section aria-labelledby="related" class="flex flex-col gap-4 border-t border-line pt-8">
            <h2 id="related" class="heading-display text-[28px] text-ink">{{ $relatedLabel }}</h2>
            <div class="grid grid-cols-[repeat(auto-fill,minmax(120px,1fr))] gap-3.5">
                @foreach ($setCards as $setCard)
                <a href="{{ $setCard->link() }}" aria-label="{{ $setCard->name() }}" class="card-tile">
                    <img src="{{ $setCard->imageSmall() }}" alt="" loading="lazy" class="aspect-[59/86] w-full rounded-lg object-cover">
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</x-layout>
