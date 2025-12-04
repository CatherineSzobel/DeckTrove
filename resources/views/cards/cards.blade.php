@php
$series = $series ?? 'yugioh';
$currentView = request('view', 'full');
$filterOptions = $filterOptions ?? []; // From controller
@endphp

<x-layout :js="['resources/js/card-database-core.js', 'resources/js/card-database.js']">
    <div class="container mx-auto px-4" data-series="{{ $series }}">
        <x-database-header :title="$title ?? ucfirst($series)">
            <!-- Your existing header with view switcher -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div class="flex gap-4 flex-wrap items-center">
                    <!-- Dynamic View Switcher -->
                    <div class="flex gap-2">
                        @foreach(['full' => 'Full', 'images' => 'Images', 'list' => 'List'] as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery(['view' => $key, 'page' => 1]) }}"
                            class="px-3 py-1 rounded text-sm {{ $currentView === $key ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $label }}
                        </a>
                        @endforeach
                    </div>

                    <!-- Filter button (only for Yugioh) -->
                    <button id="filter-button" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-colors">
                        Filter
                    </button>
                    <button id="clear-filter-button" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors">
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

                    <input type="text"
                        name="search"
                        placeholder="Search {{ ucfirst($series) }} cards..."
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50"
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
        <div id="card-container">
            @include("cards.{$series}.view.cards-{$currentView}", ['cards' => $cards])
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $cards->appends(request()->query())->links() }}
        </div>
    </div>


</x-layout>