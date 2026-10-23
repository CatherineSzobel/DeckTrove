{{-- Your collection or wishlist ($page: App\Collection\CollectionPage): Owned / Wishlist, the series, a search, totals and the cards. --}}
<x-layout :title="$page->title()" :js="['resources/js/collection-page.js']">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h1 class="heading-display text-[clamp(40px,5vw,56px)] text-ink">{{ $page->heading() }}</h1>
            <x-segment-links :links="$page->listLinks()" label="List" />
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-segment-links :links="$seriesLinks" label="Series" />
            <form method="GET" role="search" aria-label="Search your cards" class="flex min-w-0 flex-[1_1_280px] gap-2">
                <input type="hidden" name="series" value="{{ $page->series() }}">
                <label for="collection-search" class="sr-only">Search by name</label>
                <input id="collection-search" type="search" name="search" value="{{ $page->search() }}" maxlength="100" placeholder="Search by name" class="input w-full">
                <button type="submit" class="btn-secondary">Search</button>
            </form>
        </div>

        <div class="grid grid-cols-[repeat(auto-fill,minmax(min(220px,100%),1fr))] gap-3">
            @foreach ($totals as $total)
            <x-stat-tile :label="$total['label']" :value="$total['value']" />
            @endforeach
        </div>

        <p class="flex flex-wrap items-center gap-2 text-sm text-muted">
            {{ $page->privacyLine() }}
            <a href="{{ route('profile.edit') }}#privacy" class="focus-ring inline-flex min-h-11 items-center font-bold text-accent-ink">Change in Edit profile</a>
        </p>

        <x-form-error name="card" bag="collection" />

        @include('collection.partials.grid')

        @include('collection.partials.suggestions')
    </div>
</x-layout>
