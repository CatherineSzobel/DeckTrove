@php
$series = $series ?? 'yugioh';
$currentView = request('view', 'full');
$filterOptions = $filterOptions ?? [];
@endphp

<x-layout :js="['resources/js/card-database-core.js', 'resources/js/card-database.js']" :css="['resources/css/card-database.css']">
    <div class="container mx-auto px-4" data-series="{{ $series }}">

        <!-- Header / Search / Filters -->
        <x-database-header :title="$title ?? ucfirst($series)">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex gap-4 flex-wrap items-center">

                    <!-- View Switcher -->
                    <div class="flex rounded-lg bg-gray-100 p-1">
                        @foreach(['full' => 'Full', 'images' => 'Images', 'list' => 'List'] as $key => $label)
                        <button type="button"
                                data-view="{{ $key }}"
                                class="viewtype-selector px-3 py-1 text-sm rounded-md transition
                                {{ $currentView === $key ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-700 hover:bg-white' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>

                    <!-- Filter button -->
                    <button id="filter-button" class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600">Filter</button>

                    <!-- Clear filters -->
                    <button id="clear-filter-button" class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">Clear</button>

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
                <form id="search-form" class="flex gap-2">
                    <input type="hidden" name="view" value="{{ $currentView }}">
                    <input type="text" id="search-input" name="search" placeholder="Search {{ ucfirst($series) }} cards…" class="w-64 px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 bg-gray-50" value="{{ request('search', '') }}">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">Search</button>
                </form>
            </div>
        </x-database-header>

        <!-- Filters section -->
        <x-filter-section :series="$series" :options="$options"></x-filter-section>

        <!-- Cards Container -->
        <div id="card-container" class="relative mt-6 bg-gradient-to-b from-gray-200 via-gray-100 to-gray-50 p-6 rounded-xl">

            <div id="cards-inner">
                @if($cards->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-2xl bg-white py-24 text-center shadow-sm">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">No cards found</h2>
                    <p class="text-gray-500 mb-6 max-w-md">Try adjusting your search or clearing filters to see more results.</p>
                </div>
                @else
                @include('cards.partials.cards-inner', ['cards' => $cards, 'series' => $series, 'currentView' => $currentView])
                @endif
            </div>

            <!-- Loader -->
            <div id="cards-loader" class="fixed inset-0 bg-gray-900/10 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200 ease-out z-50">
                <div class="bg-blue-600 rounded-lg p-4 flex items-center gap-2">
                    <div class="loader"></div>
                    <p class="text-white font-bold">Loading cards...</p>
                </div>
            </div>

        </div>
    </div>
</x-layout>
