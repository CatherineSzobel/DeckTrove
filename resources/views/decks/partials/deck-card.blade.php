{{--
    A paginated grid of deck cards (x-deck.card). Used by the public deck page (and its AJAX response) and by public
    profiles: `variant` picks the card style, `trending` shows the "+N this week" chips, and `decksPath` points the
    page links at the page it's on.
--}}
<div class="grid grid-cols-[repeat(auto-fill,minmax(min(300px,100%),1fr))] gap-6">
    @foreach ($decks as $deck)
    <x-deck.card :deck="$deck" :variant="$variant ?? 'public'" :trending="$trending ?? false" :heading="$heading ?? 'h2'" />
    @endforeach
</div>

@if ($decks->hasPages())
<div class="mt-8">{{ $decks->withPath($decksPath ?? route('public-deck'))->links() }}</div>
@endif
