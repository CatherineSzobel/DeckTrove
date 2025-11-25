<x-layout :js="['resources/js/deck-builder.js']" :css="['resources/css/deckbuilder.css']">

    <div id="deckApp"
        data-game="{{ $game }}"
        class="grid grid-cols-3 gap-4">

        <!-- LEFT: Card Search -->
        <div class="col-span-2">
            <button id="resetButton">Reset</button>
            <!-- Main Deck -->
            <div class="p-3 bg-white rounded shadow deckContainer active">
                <h2 class="font-bold text-lg mb-2 bg-white">
                    Main Deck (<span id="mainCount">0</span>)
                </h2>
                <div id="mainDeck" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px]"></div>
            </div>


            @if ($game === 'yugioh')
            <!-- Extra Deck -->
            <div class="p-3 bg-white rounded shadow deckContainer">
                <h2 class="font-bold text-lg mb-2 bg-white">
                    Extra Deck (<span id="extraCount">0</span>)
                </h2>
                <div id="extraDeck" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px] "></div>
            </div>

            <!-- Side Deck -->
            <div class="p-3 bg-white rounded shadow deckContainer">
                <h2 class="font-bold text-lg mb-2 bg-white">
                    Side Deck (<span id="sideCount">0</span>)
                </h2>
                <div id="sideDeck" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px]"></div>
            </div>
            @endif
        </div>

        <!-- RIGHT: Deck Zones -->
        <div class="space-y-4 z-[999]">

            @include("decks.$game.cards-view", ['cards' => $cards])

            <!-- Loading spinner -->
            <div id="loadingSpinner" class="text-center my-4 hidden">
                <p>Loading more cards...</p>
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