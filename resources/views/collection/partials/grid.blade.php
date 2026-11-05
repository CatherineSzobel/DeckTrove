{{-- The cards of a collection or wishlist page ($page: App\Collection\CollectionPage), with − / + when $page->editable(). --}}
@if ($page->cards()->isEmpty())
<x-empty-state :title="$page->emptyTitle()" heading="h2" />
@else
<ul class="grid grid-cols-[repeat(auto-fill,minmax(min(160px,100%),1fr))] gap-5">
    @foreach ($page->cards() as $item)
    <li id="card-{{ $item->external_id }}" class="pack-tile flex scroll-mt-24 flex-col gap-3 p-3">
        <a href="{{ route('cards.show', [$item->game, $item->external_id]) }}" class="focus-ring flex flex-col gap-2 rounded-md">
            <img src="{{ $item->image_url }}" alt="" loading="lazy" class="aspect-[59/86] w-full rounded-md object-cover">
            <span class="flex items-baseline justify-between gap-2">
                <span class="min-w-0 truncate text-[15px] font-bold text-ink">{{ $item->name }}</span>
                @unless ($page->editable())
                <span class="shrink-0 font-mono text-sm font-bold text-accent-ink">{{ $item->copies_label }}</span>
                @endunless
            </span>
        </a>
        @if ($page->editable())
        <x-collection.counter :game="$item->game" :card="$item->external_id" :list="$page->list()" :count="$item->copies" :name="$item->name"
            :removable="$item->removable" />
        @if ($item->choose_printing_url)
        <a href="{{ $item->choose_printing_url }}" class="focus-ring flex min-h-11 items-center justify-center rounded-sm text-sm font-bold text-accent-ink">Choose a printing</a>
        @endif
        @endif
    </li>
    @endforeach
</ul>
<x-pagination :paginator="$page->cards()" />
@endif
