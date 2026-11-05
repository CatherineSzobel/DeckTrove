{{-- A player’s public collection or wishlist ($page: App\Collection\CollectionPage, read-only), by series. --}}
<x-layout :title="$page->title($user)">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-muted">
            <a href="{{ route('users.show', $user) }}" class="focus-ring text-body hover:text-ink">{{ $user->username }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-ink">{{ $page->listName() }}</span>
        </nav>
        <h1 class="heading-display text-[44px] text-ink">{{ $page->listName() }}</h1>
        <x-segment-links :links="$seriesLinks" label="Series" />
        @include('collection.partials.grid')
    </div>
</x-layout>
