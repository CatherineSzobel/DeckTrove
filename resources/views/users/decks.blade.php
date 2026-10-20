{{-- All of a player's public decks (/u/{name}/decks): series and sort links, 12 per page. --}}
<x-layout :title="$page->title()">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        @include('users.partials.breadcrumb')

        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="heading-display break-words text-[44px] text-ink">{{ $page->heading() }}</h1>
            @if ($page->listCount() > 0)
            @include('users.partials.deck-filters')
            @endif
        </div>

        @if ($page->decks()->isNotEmpty())
        @include('decks.partials.deck-card', [
            'decks' => $page->decks(),
            'variant' => 'profile',
            'heading' => 'h2',
            'trending' => $page->trending(),
            'decksPath' => route('users.decks', $user),
        ])
        @elseif ($page->listCount() > 0)
        <x-empty-state title="No decks in this series">
            <x-slot:actions><a href="{{ $page->deckQuery()->url(['game' => null]) }}" class="btn-secondary">Show all series</a></x-slot:actions>
        </x-empty-state>
        @elseif ($page->isOwner())
        <x-empty-state title="You haven’t shared a deck yet">
            Make a deck public from the deck builder and it shows up here for other players.
            <x-slot:actions><a href="{{ route('decks.mine') }}" class="btn-primary">Go to my decks</a></x-slot:actions>
        </x-empty-state>
        @else
        <x-empty-state title="No public decks yet">{{ $user->username }} hasn’t shared any decks yet.</x-empty-state>
        @endif
    </div>
</x-layout>
