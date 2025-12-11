<x-layout class="bg-gradient-to-b from-gray-100 to-gray-400" :hideNav="true">
    <div class="min-h-screen bg-gradient-to-b from-gray-100 to-gray-400 px-6">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="text-6xl font-extrabold text-gray-900 mb-6 drop-shadow-lg">
                Welcome to DeckTrove
            </h1>
            <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                Explore your favorite trading card games, build unique decks, and share them with the global community. Stay ahead with the latest series and exclusive updates!
            </p>
        </div>
        <!-- Call to Action Section -->
        <div class="mt-20 text-center">
            <p class="text-gray-800 text-lg mb-4">
                Ready to start building your ultimate deck?
            </p>
        </div>
        <!-- Login / Register Buttons -->
        <div class="flex justify-center items-center gap-6 mb-6">
            <a href="/login" class="bg-amber-400 hover:bg-amber-500 text-slate-900 font-semibold px-6 py-3 rounded-md shadow-md transition-colors duration-300">
                Login
            </a>
            <a href="/register" class="bg-amber-400 hover:bg-amber-500 text-slate-900 font-semibold px-6 py-3 rounded-md shadow-md transition-colors duration-300">
                Register
            </a>
        </div>

        <!-- Slide Cards Container -->
        <div class="flex justify-center items-center gap-6 max-w-6xl mx-auto flex-wrap">

            @php
            $cards = [
            ['src' => 'yugioh.png', 'series' => 'Yu-Gi-Oh', 'comingSoon' => false, 'link' => 'yugioh', 'description' => 'Duel your way to victory!'],
            ['src' => 'magic.png', 'series' => 'MTG', 'comingSoon' => false, 'link' => 'magic', 'description' => 'Cast spells and control the battlefield!'],
            ['src' => 'pokemon.png', 'series' => 'Pokémon', 'comingSoon' => true, 'link' => 'pokemon', 'description' => 'Catch ’em all soon!'],
            ['src' => 'digimon.png', 'series' => 'Digimon', 'comingSoon' => true, 'link' => 'digimon', 'description' => 'Digital monsters arriving soon!'],
            ];
            @endphp

            @foreach ($cards as $card)
            <div class="relative flex-1 h-96 min-w-[240px] rounded-3xl overflow-hidden border-4 border-gray-800 shadow-2xl transition-[flex] duration-500 ease-in-out hover:flex-[2.5] group">

                <!-- Clickable dashboard-logo for non-coming-soon cards -->
                @if (!$card['comingSoon'])
                <img
                    src="{{ Vite::asset('resources/img/' . $card['src']) }}"
                    alt="{{ $card['series'] }} Logo"
                    data-series="{{ $card['link'] }}"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 rounded-3xl dashboard-logo cursor-pointer" />
                @else
                <img
                    src="{{ Vite::asset('resources/img/' . $card['src']) }}"
                    alt="{{ $card['series'] }} Logo"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 rounded-3xl dashboard-logo" />
                @endif

                <!-- Coming Soon Overlay -->
                @if ($card['comingSoon'])
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white text-xl font-bold rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                    Coming Soon
                </div>
                @endif

                <!-- Description Overlay (all cards) -->
                <div class="absolute top-0 left-0 right-0 bg-black bg-opacity-50 text-white text-center font-bold text-sm py-2 px-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-t-3xl pointer-events-none">
                    {{ $card['description'] }}
                </div>

                <!-- Series Label -->
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent text-white text-center font-semibold py-2">
                    {{ $card['series'] }}
                </div>
            </div>
            @endforeach

        </div>

    </div>
</x-layout>