{{-- Quick add ($suggestions: App\Collection\CollectionSuggestions): the card database's matches for your search that
    aren't on this list yet, each with a counter. The tiles carry the card's tile id, so the no-JS redirect lands on it
    (in the grid, once it's added). --}}
@if ($suggestions->shows())
<section aria-labelledby="suggestions-heading" class="flex flex-col gap-4">
    <h2 id="suggestions-heading" class="heading-display text-[28px] text-ink">{{ $suggestions->heading() }}</h2>
    @if ($suggestions->unavailable())
    <p class="text-muted">The card search isn’t available right now.</p>
    @elseif ($suggestions->cards()->isEmpty())
    <p class="text-muted">No other cards match.</p>
    @else
    <ul class="grid grid-cols-[repeat(auto-fill,minmax(min(140px,100%),1fr))] gap-4">
        @foreach ($suggestions->cards() as $card)
        <li id="card-{{ $card->id() }}" data-suggestion class="pack-tile flex scroll-mt-24 flex-col gap-2 p-2.5">
            <a href="{{ route('cards.show', [$page->series(), $card->id()]) }}" class="focus-ring flex flex-col gap-2 rounded-md">
                <img src="{{ $card->imageSmall() }}" alt="" loading="lazy" class="aspect-[59/86] w-full rounded-md object-cover">
                <span class="truncate text-sm font-bold text-ink">{{ $card->name() }}</span>
            </a>
            <x-collection.counter :game="$page->series()" :card="$card->id()" :name="$card->name()" :list="$page->list()" :count="0" />
        </li>
        @endforeach
    </ul>
    @endif
</section>
@endif
