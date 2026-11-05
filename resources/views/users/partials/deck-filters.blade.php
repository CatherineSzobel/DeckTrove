{{-- Series and sort links of the decks and liked pages (they stay on the page). --}}
<div class="flex flex-wrap gap-2.5">
    <x-segment-links :links="$page->deckQuery()->seriesLinks()" label="Series" as="nav" key-as="game" current="true" bg="bg-surface" />
    <x-segment-links :links="$page->deckQuery()->sortLinks()" label="Sort decks" as="nav" key-as="sort" current="true" bg="bg-surface" link-class="max-sm:flex-1" class="max-sm:w-full" />
</div>
