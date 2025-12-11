<x-layout>
    <div class="text-center my-8">
        <h1 class="text-4xl font-extrabold underline text-gray-900">
            Public Decks
        </h1>
    </div>

    <!-- Dropdown to filter decks -->
    <div class="flex justify-center mb-8">
        <select id="game-filter" class="border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="all">All Series</option>
            @foreach(['Yu-Gi-Oh!', 'Pokemon', 'Magic: The Gathering'] as $game)
            <option value="{{ $game }}">{{ $game }}</option>
            @endforeach
        </select>
    </div>

    <!-- Deck grid -->
    <div id="deck-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 px-4">
        @include('decks.show', ['decks' => $decks])
    </div>

    <!-- AJAX script -->
    <script>
        filter.addEventListener('change', async () => {
            const value = filter.value;

            try {
                // Use the correct route name
                const response = await fetch(`{{ route('public-deck.filter') }}?game=${value}`);
                const data = await response.json();
                deckGrid.innerHTML = data.html;
            } catch (error) {
                console.error('Error fetching decks:', error);
            }
        });
    </script>
</x-layout>