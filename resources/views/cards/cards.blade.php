@php
$series = $series ?? 'yugioh';
$currentView = request('view', 'full');
$filterOptions = $filterOptions ?? []; // Initialize with an empty array
@endphp

<x-layout :js="['resources/js/card-database-core.js', 'resources/js/card-database.js']">
    <div class="container mx-auto px-4" data-series="{{ $series }}">
        <x-database-header :title="$title ?? ucfirst($series)">
            <!-- Your existing header with view switcher -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex gap-4 flex-wrap items-center">
                    <!-- Dynamic View Switcher -->
                    <div class="flex rounded-lg bg-gray-100 p-1">
                        @foreach(['full' => 'Full', 'images' => 'Images', 'list' => 'List'] as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery(['view' => $key, 'page' => 1]) }}"
                            class="px-3 py-1 text-sm rounded-md transition
           {{ $currentView === $key
              ? 'bg-blue-600 text-white shadow-sm'
              : 'text-gray-700 hover:bg-white' }}">
                            {{ $label }}
                        </a>
                        @endforeach
                    </div>


                    <!-- Filter button -->
                    <button id="filter-button"
                        class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-blue-50">
                        Filter
                    </button>

                    <button id="clear-filter-button"
                        class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">
                        Clear
                    </button>


                    <!-- Results count -->
                    <div class="text-sm text-gray-600">
                        @if(isset($cards) && method_exists($cards, 'total'))
                        Showing {{ $cards->firstItem() }}-{{ $cards->lastItem() }} of {{ $cards->total() }} cards
                        @else
                        Loading cards...
                        @endif
                    </div>
                </div>

                <!-- Search form -->
                <form method="GET" action="{{ route("{$series}.cards.index") }}" class="flex gap-2">
                    <input type="hidden" name="view" value="{{ $currentView }}">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search {{ ucfirst($series) }} cards…"
                        class="w-64 px-4 py-2 rounded-lg border
                        focus:ring-2 focus:ring-blue-500
                        bg-gray-50"
                        value="{{ request('search', '') }}">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Search
                    </button>

                    <!-- Clear search if there's a search term -->
                    @if(request('search'))
                    <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => 1]) }}"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Clear
                    </a>
                    @endif
                </form>
            </div>
        </x-database-header>

        <!-- Filter Section with populated options -->
        @switch($series)
        @case('yugioh')
        <x-filter-section
            :series="$series"
            :options="[
                            $filterOptions['type'] ?? [],
                            $filterOptions['attribute'] ?? [],
                            $filterOptions['race'] ?? [],
                            $filterOptions['archetype'] ?? []
                        ]">
        </x-filter-section>
        @break
        @case('magic')
        <x-filter-section
            :series="$series"
            :options="[
                            $filterOptions['type'] ?? [],
                            $filterOptions['color'] ?? [],
                            $filterOptions['rarity'] ?? [],
                            $filterOptions['set_name'] ?? []
                        ]">
        </x-filter-section>
        @break
        @default
        <x-filter-section
            :series="$series"
            :options="[
                            $filterOptions['type'] ?? [],
                            $filterOptions['attribute'] ?? [],
                            $filterOptions['race'] ?? [],
                            $filterOptions['archetype'] ?? []
                        ]">
        </x-filter-section>
        @endswitch


        <!-- Dynamic Cards Container -->
        <div id="card-container"
            class="relative mt-6 bg-gradient-to-b from-gray-200 via-gray-100 to-gray-50 p-6 rounded-xl">

            @if($cards->isEmpty())
            <div class="flex flex-col items-center justify-center
                    rounded-2xl bg-white
                    py-24 text-center shadow-sm">

                <h2 class="text-2xl font-semibold text-gray-800 mb-2">
                    No cards found
                </h2>

                <p class="text-gray-500 mb-6 max-w-md">
                    Try adjusting your search or clearing filters to see more results.
                </p>

                <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => 1]) }}"
                    class="inline-flex items-center gap-2
                      px-5 py-2.5
                      rounded-lg bg-blue-600 text-white
                      hover:bg-blue-700 transition">
                    Clear search
                </a>
            </div>
            @else
            <div class="max-w-7xl mx-auto">
            @include("cards.{$series}.view.cards-{$currentView}", ['cards' => $cards])
            </div>
            @endif
        </div>

        @if($cards->hasPages())
        <div class="mt-16 flex justify-center">
            <div class="rounded-2xl bg-white shadow-sm
                px-10 py-8">
                {{ $cards->links() }}
            </div>
        </div>
        @endif
    </div>
</x-layout>