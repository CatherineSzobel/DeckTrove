<x-layout js="/resources/js/deck-builder.js">

    <div id="deckApp"
        data-game="{{ $game }}"
        class="grid grid-cols-3 gap-4">

        <!-- LEFT: Card Search -->
        <div class="col-span-2">

            <!-- MAIN DECK -->
            <div class="p-3 bg-white rounded shadow">
                <h2 class="font-bold text-lg mb-2">
                    Main Deck (<span id="mainCount">0</span>)
                </h2>
                <div id="mainDeck"
                    class="min-h-[150px] grid grid-cols-3 gap-2">
                </div>
            </div>

            @if ($game === 'yugioh')
            <!-- EXTRA DECK -->
            <div class="p-3 bg-white rounded shadow">
                <h2 class="font-bold text-lg mb-2">
                    Extra Deck (<span id="extraCount">0</span>)
                </h2>
                <div id="extraDeck"
                    class="min-h-[150px] grid grid-cols-3 gap-2">
                </div>
            </div>

            <!-- SIDE DECK -->
            <div class="p-3 bg-white rounded shadow">
                <h2 class="font-bold text-lg mb-2">
                    Side Deck (<span id="sideCount">0</span>)
                </h2>
                <div id="sideDeck"
                    class="min-h-[150px] grid grid-cols-3 gap-2">
                </div>
            </div>
            @endif

        </div>

        <!-- RIGHT: Deck Zones -->
        <div class="space-y-4">
            @if ($game === 'yugioh')
            <div class="p-3 bg-white rounded shadow">
                @foreach ($cards as $card)
                <div class="relative w-32 h-44 mb-4 mx-auto group">
                    <!-- Card Image -->
                    <img
                        src="{{ $card['card_images'][0]['image_url_small'] ?? $card['card_images'][0]['image_url'] ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                        alt="{{ $card['name'] }}"
                        class="w-full h-full object-cover rounded card">


                    <!-- Hover Overlay (on top of card) -->
                    <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 group-hover:opacity-100 transition-opacity rounded p-2 flex flex-col justify-center items-center text-center">
                        <a href="{{ url('/yugioh/card/' . $card['id']) }}" target="_blank">
                            <h3 class="font-bold text-xs">{{ $card['name'] }}</h3>
                        </a>
                        <p class="text-[10px] mt-1">{{ $card['type'] ?? 'Unknown' }} / {{ $card['race'] ?? 'Unknown' }}</p>
                    </div>

                    <!-- Description (appears to the right on hover) -->
                    <div class="absolute top-0 left-full ml-2 w-48 bg-black bg-opacity-80 text-white text-xs p-2 rounded opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <p>{{ $card['desc'] ?? 'No description available' }}</p>
                    </div>
                </div>
                @endforeach
            </div>



            @elseif ($game === 'magic')
            <div class="p-3 bg-white rounded shadow">
                @foreach ($cards as $card)
                <div class="relative w-32 h-44 mb-4 mx-auto group">
                    <!-- Card Image -->
                    <img
                        src="{{ $card['image_uris']['small'] ?? ($card['card_faces'][0]['image_uris']['small'] ?? '') }}"
                        alt="{{ $card['name'] }}"
                        class="w-full h-full object-cover rounded card">
                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 group-hover:opacity-100 transition-opacity rounded p-2 flex flex-col justify-center items-center text-center">
                        <a href="{{ url('/magic/card/' . $card['id']) }}" target="_blank">
                            <h3 class=" font-bold text-sm">{{ $card['name'] }}</h3>
                        </a>
                        <p class="text-xs mt-1">{{ $card['type_line'] ?? $card['desc'] ?? '' }}</p>
                    </div>
                    <!-- Description (appears to the right on hover) -->
                    <div class="absolute top-0 left-full ml-2 w-48 bg-black bg-opacity-80 text-white text-xs p-2 rounded opacity-0 group-hover:opacity-100 transition-opacity z-10">
                        <p>{{ $card['oracle_text'] ?? 'No description available' }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            @endif

            <!-- Pagination -->
            <div class="mt-6">
                {{ $cards->appends(request()->query())->links() }}
            </div>

            <!-- SAVE BUTTON -->
            <form id="saveForm" action="/{{ $game }}/deck-builder/save" method="post">
                @csrf
                <input type="hidden" name="cards" id="cardData">

                <button class="mt-4 w-full bg-blue-600 text-white p-3 rounded" disabled>
                    Save Deck
                </button>
            </form>

        </div>

    </div>

</x-layout>