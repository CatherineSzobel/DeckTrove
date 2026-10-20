{{-- All public decks a player liked (/u/{name}/liked), with their owners. Only the owner while it is private. --}}
<x-layout :title="$page->title()">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        @include('users.partials.breadcrumb')

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="heading-display break-words text-[44px] text-ink">{{ $page->heading() }}</h1>
                @if ($page->likedIsPrivate())
                @include('users.partials.private-badge')
                @endif
            </div>
            @if ($page->listCount() > 0)
            @include('users.partials.deck-filters')
            @endif
        </div>

        @if ($page->decks()->isNotEmpty())
        @include('decks.partials.deck-card', [
            'decks' => $page->decks(),
            'variant' => 'public',
            'heading' => 'h2',
            'trending' => $page->trending(),
            'decksPath' => route('users.liked', $user),
        ])
        @elseif ($page->listCount() > 0)
        <x-empty-state title="No decks in this series">
            <x-slot:actions><a href="{{ $page->deckQuery()->url(['game' => null]) }}" class="btn-secondary">Show all series</a></x-slot:actions>
        </x-empty-state>
        @elseif ($page->isOwner())
        <x-empty-state title="You haven’t liked any decks yet">
            Like a deck on Public decks and it shows up here.
            <x-slot:actions><a href="{{ route('decks.index') }}" class="btn-primary">Browse public decks</a></x-slot:actions>
        </x-empty-state>
        @else
        <x-empty-state title="No liked decks yet">{{ $user->username }} hasn’t liked any decks yet.</x-empty-state>
        @endif
    </div>
</x-layout>
