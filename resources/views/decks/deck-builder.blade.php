<x-layout :js="['resources/js/deck-builder.js']" :css="['resources/css/deckbuilder.css']">

    <div id="deckApp"
        data-game="{{ $game }}"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <!-- LEFT: Card Search -->
        <div class="col-span-1 md:col-span-2">
            <div class="p-3 bg-white rounded shadow">
                <input type="text" id="deckTitleInput" placeholder="Enter deck title..." class="w-full p-2 border rounded" value="{{ request('deck_title') }}">
                <input type="text" id="deckDescInput" placeholder="Enter deck description..." class="w-full p-2 border rounded mt-2" value="{{ request('deck_description') }}">
                <button id="resetButton">Reset</button>
            </div>

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
            <div class="flex w-full md:w-[400px] lg:w-[600px] gap-2">
                <!-- Card Search - 80% -->
                <div class="flex-[4] p-3 bg-white rounded shadow">
                    <h2 class="font-bold text-lg mb-2 bg-white">Card Search</h2>
                    <div class="text-sm text-gray-600">
                        @if(isset($cards) && method_exists($cards, 'total'))
                        Showing {{ $cards->firstItem() }}-{{ $cards->lastItem() }} of {{ $cards->total() }} cards
                        @else
                        Loading cards...
                        @endif
                    </div>
                    <input type="text" id="searchInput" placeholder="Search cards..." class="w-full p-2 border rounded" value="{{ request('search') }}">
                </div>

                <!-- Buttons - 20% -->
                <div class="flex-[1] p-3 bg-white rounded shadow flex flex-col gap-2">
                    <button id="filter-button" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-colors">
                        Filter
                    </button>
                    <button id="clear-filter-button" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors">
                        Clear
                    </button>
                </div>
            </div>

            @include("decks.$game.cards-view", ['cards' => $cards])

            <!-- Loading spinner -->
            <div id="loadingSpinner" class="text-center my-4 hidden">
                <p>Loading more cards...</p>
            </div>

            <!-- SAVE BUTTON -->
            <form id="saveForm" action="{{ route($game . '.deck.builder.save') }}" method="post" class="flex w-full md:w-[400px] lg:w-[600px]">
                @csrf
                <input type="hidden" name="cards" id="cardData">
                <input type="hidden" name="game" value="{{ $game }}">
                <input type="hidden" name="deck_title" id="deckTitle" value="{{ request('deck_title') }}">
                <input type="hidden" name="deck_description" id="deckDescription" value="{{ request('deck_description') }}">

                <button class="mt-4 bg-blue-600 text-white p-3 rounded hover:bg-blue-700 transition-colors w-full">
                    Save Deck
                </button>
            </form>

        </div>
        <!-- Deck Warning Modal -->
        <div id="deckWarningModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded p-6 w-96 max-w-full shadow-lg">
                <h2 class="text-lg font-bold mb-4">Deck Too Small</h2>
                <p class="mb-4">Your main deck has less than 40 cards. This deck cannot be added to the public deck. Do you want to continue saving as a private deck?</p>
                <div class="flex justify-end gap-2">
                    <button id="cancelSaveBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button id="confirmSaveBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Continue</button>
                </div>
            </div>
        </div>

    </div>

</x-layout>