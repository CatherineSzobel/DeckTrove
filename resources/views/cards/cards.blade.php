@php $label = config("series.$series.label"); @endphp

<x-layout :title="$label.' cards'" :js="['resources/js/card-database.js']">
    <div class="container mx-auto px-4" data-series="{{ $series }}">

        <x-database-header :title="$label">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex gap-4 flex-wrap items-center">

                    <div class="flex rounded-lg bg-gray-100 p-1" role="group" aria-label="Card view">
                        @foreach (['full' => 'Full', 'images' => 'Images', 'list' => 'List'] as $key => $viewLabel)
                        <button type="button" data-view="{{ $key }}" aria-pressed="{{ $currentView === $key ? 'true' : 'false' }}"
                            @class([
                                'viewtype-selector px-3 py-1 text-sm rounded-md transition',
                                'bg-blue-600 text-white shadow-sm' => $currentView === $key,
                                'text-gray-700 hover:bg-white' => $currentView !== $key,
                            ])>{{ $viewLabel }}</button>
                        @endforeach
                    </div>

                    <x-filter-button />
                    <button type="button" id="clear-filter-button" class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">Clear</button>

                    <div id="result-count" class="text-sm text-gray-600" aria-live="polite">
                        @include('cards.partials.result-count')
                    </div>
                </div>

                <form id="search-form" class="flex gap-2" role="search">
                    <input type="search" id="search-input" name="search" value="{{ request('search', '') }}"
                        placeholder="Search {{ $label }} cards…" aria-label="Search cards"
                        class="w-64 px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 bg-gray-50">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">Search</button>
                </form>
            </div>
        </x-database-header>

        <x-filter-section :series="$series" :options="$options" />

        <div id="card-container" class="relative mt-6 bg-gradient-to-b from-gray-200 via-gray-100 to-gray-50 p-6 rounded-xl">
            <div id="cards-inner">
                @include('cards.partials.cards-inner')
            </div>

            <div id="cards-loader" class="fixed inset-0 bg-gray-900/10 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200 ease-out z-50" aria-hidden="true">
                <div class="bg-blue-600 rounded-lg p-4 flex items-center gap-2">
                    <div class="loader"></div>
                    <p class="text-white font-bold">Loading cards...</p>
                </div>
            </div>
        </div>
    </div>
</x-layout>
