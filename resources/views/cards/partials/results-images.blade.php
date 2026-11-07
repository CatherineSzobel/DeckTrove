{{-- Images view: card images; the name and type show on hover and keyboard focus (.card-tile in app.css), and the
    description in the shared card preview (cardPreview() in resources/js/deckbuilder/preview.js, panel in cards.blade.php). --}}
<div class="grid grid-cols-[repeat(auto-fill,minmax(140px,1fr))] gap-3 sm:grid-cols-[repeat(auto-fill,minmax(160px,1fr))] sm:gap-5">
    @foreach ($cards as $card)
    <div id="card-{{ $card->id() }}" class="relative scroll-mt-24">
        <x-card-faces-badge :card="$card" corner />
        <a href="{{ $card->link() }}" class="card-tile" x-data="{ card: @js($card->builderCard()) }" x-bind="previewTrigger">
            <img src="{{ $card->imageSmall() }}" alt="{{ $card->name() }}" loading="lazy" class="aspect-[59/86] w-full rounded-lg object-cover">
            <span class="card-tile-overlay" aria-hidden="true">
                <span class="font-display text-xl font-bold leading-tight">{{ $card->name() }}</span>
                <span class="font-mono text-[11px] opacity-80">{{ $card->typeLine() }}</span>
            </span>
        </a>
        @if ($tileCounts->shows())
        <x-collection.tile-counter :counts="$tileCounts" :card="$card" :series="$series" class="mt-1" />
        @endif
    </div>
    @endforeach
</div>
