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
        <div id="card-container" class="relative mt-6 px-6 py-16 rounded-lg
            bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50
            shadow-inner">
            @if($cards->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <h2 class="text-2xl font-semibold text-gray-700 mb-2">
                    Card cannot be found
                </h2>

                <p class="text-gray-500 mb-6">
                    Try adjusting your search or clearing filters.
                </p>

                <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => 1]) }}"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    Clear Search
                </a>
            </div>
            @else
            @include("cards.{$series}.view.cards-{$currentView}", ['cards' => $cards])
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $cards->appends(request()->query())->links() }}
        </div>
    </div>


</x-layout>