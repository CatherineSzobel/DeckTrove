{{-- Images view: card images; the name and type show on hover and keyboard focus (.card-tile in app.css), and the
    description in the shared card preview (cardPreview() in resources/js/deckbuilder/preview.js, panel in cards.blade.php). --}}
<div data-results class="grid grid-cols-[repeat(auto-fill,minmax(140px,1fr))] gap-3 sm:grid-cols-[repeat(auto-fill,minmax(160px,1fr))] sm:gap-5">
    @foreach ($cards as $card)
    <a href="{{ $card->link() }}" class="card-tile" x-data="{ card: @js($card->builderCard()) }"
        @mouseenter="showPreview(card, $el)" @mouseleave="hidePreview()" @focus="showPreview(card, $el)" @blur="hidePreview()">
        <img src="{{ $card->imageSmall() }}" alt="{{ $card->name() }}" loading="lazy" class="aspect-[59/86] w-full rounded-lg object-cover">
        <span class="card-tile-overlay" aria-hidden="true">
            <span class="font-display text-xl font-bold leading-tight">{{ $card->name() }}</span>
            <span class="font-mono text-[11px] opacity-80">{{ $card->typeLine() }}</span>
        </span>
    </a>
    @endforeach
</div>
