<x-layout :js="['resources/js/deck-builder.js']" :css="['resources/css/deckbuilder.css']">

    <div class="relative">
        @guest
        <div class="fixed inset-0 z-[1000] flex items-center justify-center bg-gradient-to-br from-black/70 via-black/60 to-black/70">

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8 text-center">

                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7 text-blue-600"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V7a5 5 0 00-10 0v4H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Login Required
                </h2>

                <p class="text-gray-600 mb-6">
                    Sign in to build decks, save progress, and share your creations.
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <a href="{{ route('login') }}"
                        class="inline-flex justify-center items-center px-6 py-2.5 rounded-full
                      bg-blue-600 text-white font-semibold
                      hover:bg-blue-700 transition-colors">
                        Login
                    </a>

                    <a href="{{ route('register') }}"
                        class="inline-flex justify-center items-center px-6 py-2.5 rounded-full
                      border border-gray-300 text-gray-700 font-semibold
                      hover:bg-gray-100 transition-colors">
                        Create account
                    </a>
                </div>

                <p class="mt-6 text-xs text-gray-400">
                    Your work will be available after you sign in.
                </p>

            </div>
        </div>

        @endguest

        <div
            id="deckApp"
            data-game="{{ $game }}"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4
           @guest pointer-events-none opacity-50 @endguest">

            <div class="col-span-1 md:col-span-2">
                @if ($errors->any())
                <p class="text-center p-2 font-bold text-red-500">
                    {{ $errors->first() }}
                </p>
                @endif

                <div class="p-3 bg-white rounded shadow" id="deckCoverContainer">
                    <input id="deckTitleInput" class="input" placeholder="Enter deck title..." value="{{ request('deck_title') }}">
                    <input id="deckDescInput" class="input mt-2" placeholder="Enter deck description..." value="{{ request('deck_description') }}">

                    <div id="coverDropArea"
                        class="mt-2 p-4 border-2 border-dashed border-gray-400 rounded text-center text-gray-600">
                        Drag a card here to set as deck cover
                    </div>

                    <img id="coverPreview" class="hidden mt-2 w-32 mx-auto rounded shadow">

                    <div class="flex justify-between items-center mt-3">
                        <button id="resetButton" class="text-sm text-gray-600 hover:text-red-600">
                            Reset
                        </button>

                        <label class="flex items-center gap-2 font-semibold">
                            <input type="checkbox" id="isPublicCheckbox" {{ request('is_public') ? 'checked' : '' }}>
                            Public
                        </label>
                    </div>
                </div>

                <div class="p-3 bg-white rounded shadow deckContainer active">
                    <h2 class="font-bold text-lg mb-2 bg-white">
                        Main Deck (<span id="mainCount">0</span>)
                    </h2>
                    <div id="main" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px]"></div>
                </div>


                @if ($game === 'yugioh')
                <div class="p-3 bg-white rounded shadow deckContainer">
                    <h2 class="font-bold text-lg mb-2 bg-white">
                        Extra Deck (<span id="extraCount">0</span>)
                    </h2>
                    <div id="extra" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px] "></div>
                </div>
                @endif

                <div class="p-3 bg-white rounded shadow deckContainer">
                    <h2 class="font-bold text-lg mb-2 bg-white">
                        Side Deck (<span id="sideCount">0</span>)
                    </h2>
                    <div id="side" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px]"></div>
                </div>
            </div>

            <div class="space-y-4 z-[999]">
                <div class="flex w-full md:w-[400px] lg:w-[600px] gap-2">
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

                    <div class="flex-[1] p-3 bg-white rounded shadow flex flex-col gap-2">
                        <button id="filter-button" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-colors">
                            Filter
                        </button>
                        <button id="clear-filter-button" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors">
                            Clear
                        </button>
                    </div>
                </div>

                @include("decks.partials.cards-view", ['cards' => $cards])

                <!-- Loading spinner -->
                <div id="loadingSpinner" class="text-center my-4 hidden">
                    <p>Loading more cards...</p>
                </div>

                <!-- SAVE BUTTON -->
                <form id="saveForm"
                    action="{{ route($game . '.deck.builder.save') }}"
                    method="POST"
                    class="w-full md:w-[400px] lg:w-[600px]">
                    @csrf

                    <input type="hidden" name="cards" id="cards">
                    <input type="hidden" name="game" value="{{ $game }}">
                    <input type="hidden" name="deck_title" id="deckTitle">
                    <input type="hidden" name="deck_description" id="deckDescription">
                    <input type="hidden" name="is_public" id="isPublic" value="0">
                    <input type="hidden" name="image" id="deckImage">

                    <button class="mt-4 w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700">
                        Save Deck
                    </button>
                </form>


            </div>
            <div id="deckWarningModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white rounded p-6 w-96 max-w-full shadow-lg">
                    <h2 class="text-lg font-bold mb-4">Deck Too Small</h2>
                    <p class="mb-4">Your main deck has less than the minimum amount of cards.
                        This deck cannot be added to the public deck.
                        Do you want to continue saving as a private deck?</p>
                    <div class="flex justify-end gap-2">
                        <button id="cancelSaveBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                        <button id="confirmSaveBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Continue</button>
                    </div>
                </div>
            </div>

        </div>

</x-layout>