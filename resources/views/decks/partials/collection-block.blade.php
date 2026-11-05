{{-- "Your collection" in the deck stats sidebar ($collection: App\Collection\DeckCollection): owned of needed, what’s missing and its cost. --}}
<section aria-labelledby="deck-collection" class="flex flex-col gap-3 border-t border-line pt-5">
    <h3 id="deck-collection" class="font-mono text-xs uppercase tracking-[0.08em] text-muted">Your collection</h3>
    <p class="text-[15px] font-bold text-ink">{{ $collection->summary() }}</p>
    <div class="h-2 overflow-hidden rounded-full bg-canvas" role="progressbar" aria-label="Cards owned" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $collection->percent() }}">
        <span class="block h-full bg-accent" style="{{ $collection->barStyle() }}"></span>
    </div>

    <x-form-error name="card" bag="collection" />

    @unless ($collection->isComplete())
    <ul class="flex flex-col gap-1 text-sm">
        @foreach ($collection->missing() as $row)
        <li class="flex justify-between gap-3"><span class="min-w-0 truncate text-body">{{ $row['name'] }}</span><span class="shrink-0 font-mono text-muted">{{ $row['line'] }}</span></li>
        @endforeach
    </ul>
    @if ($collection->costLine())
    <p class="font-mono text-sm font-bold text-ink">{{ $collection->costLine() }}</p>
    @endif
    <div class="flex flex-wrap gap-2">
        <form method="POST" action="{{ route('decks.collection', $deck) }}">
            @csrf
            <button type="submit" class="btn-secondary">Add deck to collection</button>
        </form>
        <form method="POST" action="{{ route('decks.wishlist', $deck) }}">
            @csrf
            <button type="submit" class="btn-secondary">Add missing to wishlist</button>
        </form>
    </div>
    @endunless
</section>
