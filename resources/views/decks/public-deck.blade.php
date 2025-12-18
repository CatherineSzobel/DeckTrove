<x-layout :js="['resources/js/deckfilter.js']">
    <div class="min-h-screen bg-gradient-to-b from-gray-200 via-gray-100 to-gray-50 px-6 py-12 rounded-lg">
        <div class="max-w-7xl mx-auto">

            <!-- Header -->
            <div class="text-center my-12">
                <h1 class="text-5xl font-extrabold text-gray-900 tracking-tight">
                    Public Decks
                </h1>
                <p class="mt-3 text-gray-700 max-w-xl mx-auto">
                    Browse decks shared by the community and get inspired.
                </p>
            </div>

            <!-- Filters -->
            <div class="flex justify-center mb-12">
                <select id="game-filter"
                    class="bg-white text-gray-900 border border-gray-300 rounded-xl px-5 py-3 shadow-sm focus:ring-2 focus:ring-purple-500 focus:outline-none transition-all duration-200 hover:shadow-md">
                    <option value="all">All Series</option>
                    <option value="yugioh">Yu-Gi-Oh!</option>
                    <option value="pokemon">Pokémon</option>
                    <option value="magic">Magic: The Gathering</option>
                </select>
            </div>

            <!-- Deck Grid -->
            <div id="deck-grid"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @include('decks.partials.deck-card', ['decks' => $decks])
            </div>
        </div>
    </div>
</x-layout>