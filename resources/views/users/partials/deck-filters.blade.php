{{-- Series and sort links of the decks and liked pages (they stay on the page). --}}
<div class="flex flex-wrap gap-2.5">
    <nav aria-label="Series" class="inline-flex gap-1 rounded-xl border border-line bg-surface p-1">
        <a href="{{ $page->deckQuery()->url(['game' => null]) }}" data-game="all" @if (! $page->filters()['game']) aria-current="true" @endif class="segment">All</a>
        @foreach (config('series') as $key => $config)
        <a href="{{ $page->deckQuery()->url(['game' => $key]) }}" data-game="{{ $key }}" @if ($page->filters()['game'] === $key) aria-current="true" @endif class="segment">{{ $config['label'] }}</a>
        @endforeach
    </nav>
    <nav aria-label="Sort decks" class="inline-flex gap-1 rounded-xl border border-line bg-surface p-1 max-sm:w-full">
        @foreach ($page->sorts() as $key => $label)
        <a href="{{ $page->deckQuery()->url(['sort' => $key]) }}" data-sort="{{ $key }}" @if ($page->filters()['sort'] === $key) aria-current="true" @endif
            class="segment max-sm:flex-1">{{ $label }}</a>
        @endforeach
    </nav>
</div>
