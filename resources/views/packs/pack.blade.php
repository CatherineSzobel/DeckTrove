{{--
    A pack's page: cover, facts and its cards, with a name / rarity filter. The filter is a GET form the server
    applies (App\Packs\PackCardFilter); non-matching tiles are rendered `hidden`; resources/js/pack-page.js filters
    the same tiles live and shows the shared card preview (cardPreview() in resources/js/deckbuilder/preview.js) on
    hover and keyboard focus.
--}}

<x-layout :title="$pack->name()" :js="['resources/js/pack-page.js']">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-muted">
            <a href="{{ route('packs.index', $series) }}" class="focus-ring rounded-sm text-body hover:text-ink">{{ $seriesLabel }} packs</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-ink">{{ $pack->name() }}</span>
        </nav>

        <section aria-labelledby="pack-name" class="flex flex-wrap items-center gap-8">
            <x-pack.cover :pack="$pack" size="lg" />
            <div class="flex min-w-0 flex-[1_1_420px] flex-col gap-[18px]">
                <div class="flex flex-col gap-2.5">
                    <p class="eyebrow">{{ $seriesLabel }} pack</p>
                    <h1 id="pack-name" class="heading-display text-[clamp(40px,5vw,64px)] text-ink">{{ $pack->name() }}</h1>
                </div>
                <dl class="flex flex-wrap gap-3">
                    @foreach ($pack->facts() as $fact)
                    <x-stat-box :label="$fact['label']" :value="$fact['value']" size="sm" />
                    @endforeach
                </dl>
            </div>
        </section>

        @if ($cards->isEmpty())
        <x-empty-state title="No cards in this pack yet" />
        @else
        <div x-data="packCards" @scroll.window.passive="hideOnScroll()" class="flex flex-col gap-6">
            <form method="GET" action="{{ $pack->link() }}" role="search" aria-label="Filter cards" x-ref="form" @submit.prevent="filter()"
                class="flex flex-wrap items-center gap-3 rounded-[18px] border border-line bg-surface p-4">
                <div class="relative min-w-0 flex-[1_1_320px]">
                    <label for="card-filter" class="sr-only">Filter cards by name</label>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-muted" aria-hidden="true" />
                    <input id="card-filter" type="search" name="name" value="{{ $filter->name() }}" maxlength="100"
                        placeholder="Filter cards by name" @input="filter()" class="input w-full pl-11">
                </div>
                <label for="rarity" class="sr-only">Rarity</label>
                <select id="rarity" name="rarity" @change="filter()" class="input min-w-0 flex-[0_1_220px]">
                    <option value="">All rarities</option>
                    @foreach ($rarities as $value => $label)
                    <option value="{{ $value }}" @selected($filter->isRarity($value))>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary no-js-only">Filter</button>
                <p aria-live="polite" x-text="countLabel" class="ml-auto font-mono text-[13px] text-muted">{{ $countLabel }}</p>
            </form>

            <div x-ref="grid" class="grid grid-cols-[repeat(auto-fill,minmax(min(180px,100%),1fr))] gap-5">
                @foreach ($cards as $card)
                <a href="{{ $card->link() }}" data-name="{{ $card->searchName() }}" data-rarity="{{ $card->rarity() }}" @unless ($filter->matches($card)) hidden @endunless
                    x-data="{ card: @js($card->builderCard()) }" @mouseenter="showPreview(card, $el)" @mouseleave="hidePreview()"
                    @focus="showPreview(card, $el)" @blur="hidePreview()" class="pack-tile flex flex-col gap-3 p-3">
                    <img src="{{ $card->imageSmall() }}" alt="" loading="lazy" class="aspect-[59/86] w-full rounded-md object-cover">
                    <span class="flex min-w-0 flex-col gap-1.5 px-0.5 pb-0.5">
                        <span class="truncate text-[15px] font-bold text-ink">{{ $card->name() }}</span>
                        <span class="truncate text-xs text-muted">{{ $card->typeLine() }}</span>
                        <span class="rarity-pill max-w-full self-start" title="{{ $card->rarityLabel() }}"><span class="min-w-0 truncate">{{ $card->rarityLabel() }}</span></span>
                    </span>
                </a>
                @endforeach
            </div>

            <p data-no-matches x-ref="noMatches" @unless ($noMatches) hidden @endunless class="py-8 text-center text-muted">No cards match. Try another name or rarity.</p>

            <x-deck.card-preview />
        </div>
        @endif
    </div>
</x-layout>
