<x-layout title="Public decks" :js="['resources/js/deckfilter.js']">
    <div class="min-h-screen bg-gradient-to-b from-gray-200 via-gray-100 to-gray-50 px-6 py-12 rounded-lg">
        <div class="max-w-7xl mx-auto">
            <div class="text-center my-12">
                <h1 class="text-5xl font-extrabold text-gray-900 tracking-tight">Public Decks</h1>
                <p class="mt-3 text-gray-700 max-w-xl mx-auto">Browse decks shared by the community and get inspired.</p>
            </div>

            <form method="GET" action="{{ route('public-deck') }}" class="flex justify-center mb-12">
                <label for="game-filter" class="sr-only">Filter by series</label>
                <select id="game-filter" name="game"
                    class="bg-white text-gray-900 border border-gray-300 rounded-xl px-5 py-3 shadow-sm focus:ring-2 focus:ring-purple-500 focus:outline-none transition-all duration-200 hover:shadow-md">
                    <option value="">All Series</option>
                    @foreach (config('series') as $series => $config)
                    <option value="{{ $series }}" @selected(request('game') === $series)>{{ $config['label'] }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="ml-2 px-4 rounded-xl bg-purple-600 text-white">Filter</button></noscript>
            </form>

            <div id="deck-grid">
                @include('decks.partials.deck-card')
            </div>
        </div>
    </div>
</x-layout>
