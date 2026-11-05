{{-- "In your collection" on the card page ($collection: App\Collection\CardCollectionPanel): the owned counter and the
    wishlist heart. Copies by printing are counted in the Printings list below; with JS, the row's note and border and
    the heart follow those counters (collectionRow, collectionHeart in resources/js/collection/counter.js). --}}
<section id="in-collection" aria-label="In your collection" class="collection-row flex scroll-mt-24 flex-wrap items-center justify-between gap-4 rounded-2xl border bg-surface py-1.5 pl-5 pr-1.5"
    x-data="collectionRow" data-game="{{ $series }}" data-card="{{ $card->id() }}" data-owned="{{ $collection->owned() }}" data-note="{{ $collection->note() }}"
    x-init="start($el)" @collection-changed.window="sync($event.detail)" :data-owned="owned > 0 ? owned : null">
    <span class="flex flex-col gap-0.5">
        <span class="font-bold text-ink">In your collection</span>
        <span class="text-[13px] text-muted" x-text="note">{{ $collection->note() }}</span>
    </span>
    <span class="flex items-center gap-3">
        <x-collection.counter :game="$series" :card="$card->id()" :name="$card->name()" :count="$collection->owned()" :removable="$collection->removable()" size="row" anchor="in-collection" />
        <form method="POST" action="{{ route('collection.want') }}" x-data="collectionHeart" data-wanted="{{ $collection->wantedFlag() }}" data-game="{{ $series }}" data-card="{{ $card->id() }}"
            x-init="start($el)" @submit="submit($event)" @collection-changed.window="sync($event.detail)" class="flex flex-col items-center">
            @csrf
            <input type="hidden" name="game" value="{{ $series }}">
            <input type="hidden" name="card" value="{{ $card->id() }}">
            <input type="hidden" name="want" value="{{ $collection->wantValue() }}" :value="wanted ? '0' : '1'">
            <button type="submit" aria-pressed="{{ $collection->pressed() }}" :aria-pressed="wanted.toString()" class="btn-like collection-heart group">
                <x-heroicon-o-heart class="h-5 w-5 group-aria-pressed:fill-current" aria-hidden="true" />
                <span x-text="wanted ? 'Wanted' : 'Want it'">{{ $collection->heartLabel() }}</span>
            </button>
            <span x-show="error" x-text="error" x-cloak role="alert" class="text-center text-xs text-danger"></span>
        </form>
    </span>
    <x-form-error name="card" bag="collection" class="basis-full" />
</section>
