{{-- A row of card tiles on the profile overview ($cards: rows with game, external_id, name, image_url, copies_label, label), or an empty state. --}}
@if ($cards->isNotEmpty())
<ul class="grid grid-cols-[repeat(auto-fill,minmax(min(120px,100%),1fr))] gap-3">
    @foreach ($cards as $card)
    <li>
        <a href="{{ route('cards.show', [$card->game, $card->external_id]) }}" class="card-tile focus-ring" aria-label="{{ $card->label }}">
            <img src="{{ $card->image_url }}" alt="" loading="lazy" class="aspect-[59/86] w-full rounded-lg object-cover">
        </a>
    </li>
    @endforeach
</ul>
@else
<x-empty-state :title="$empty" heading="h3" />
@endif
