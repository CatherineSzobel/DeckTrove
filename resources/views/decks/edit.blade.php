<x-layout :js="['resources/js/deck-builder.js']" :css="['resources/css/deckbuilder.css']">
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
                <input id="deckTitleInput" class="input" placeholder="Enter deck title..." value="{{ $deck->name }}">
                <input id="deckDescInput" class="input mt-2" placeholder="Enter deck description..." value="{{ $deck->description }}">

                <div id="coverDropArea"
                    class="mt-2 p-4 border-2 border-dashed border-gray-400 rounded text-center text-gray-600">
                    Drag a card here to set as deck cover
                </div>

                <img id="coverPreview"
                    class="{{ $deck->image ? '' : 'hidden' }} mt-2 w-32 mx-auto rounded shadow"
                    src="{{ $deck->image ?? Vite::asset('resources/img/decktrove-logo.png') }}"
                    alt="Deck Cover Preview">


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
                    Main Deck (<span id="mainCount">{{ count($deckCards['main']) }}</span>)
                </h2>
                <div id="main" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px]">
                    @foreach($deckCards['main'] as $card)
                    <div class="relative w-20 h-28 mb-2 mx-auto group card-wrapper cursor-pointer"
                        draggable="true"
                        data-card-id="{{ $card['id'] }}"
                        data-card-name="{{ $card['name'] ?? '' }}"
                        data-card-image="{{ $card['image_uris']['normal'] ?? $card['image'] ?? '' }}"
                        data-card-type="{{ $card['type'] ?? $card['card_type'] ?? '' }}"
                        data-card-race="{{ $card['race'] ?? '' }}"
                        data-card-desc="{{ $card['desc'] ?? $card['oracle_text'] ?? '' }}">

                        <img src="{{ $card['image_uris']['normal'] ?? $card['image'] ?? Vite::asset('resources/img/decktrove-logo.png') }}"
                            alt="{{ $card['name'] ?? 'Unknown' }}"
                            class="w-full h-full object-cover rounded card">

                        <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 
                            group-hover:opacity-100 transition-opacity rounded p-1 
                            flex flex-col justify-center items-center text-center">
                            <h3 class="font-bold text-[10px] leading-snug">{{ $card['name'] ?? 'Unknown' }}</h3>
                            <p class="text-[8px] mt-1 pointer-events-none">{{ $card['type'] ?? $card['card_type'] ?? $card['type_line'] ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @if ($game === 'yugioh')
            <div class="p-3 bg-white rounded shadow deckContainer">
                <h2 class="font-bold text-lg mb-2 bg-white">
                    Extra Deck (<span id="extraCount">{{ count($deckCards['extra']) }}</span>)
                </h2>
                <div id="extra" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px]">
                    @foreach($deckCards['extra'] as $card)
                    <div class="relative w-20 h-28 mb-2 mx-auto group card-wrapper cursor-pointer"
                        draggable="true"
                        data-card-id="{{ $card['id'] }}"
                        data-card-name="{{ $card['name'] ?? '' }}"
                        data-card-image="{{ $card['image_uris']['normal'] ?? $card['image'] ?? '' }}"
                        data-card-type="{{ $card['type'] ?? $card['card_type'] ?? '' }}"
                        data-card-race="{{ $card['race'] ?? '' }}"
                        data-card-desc="{{ $card['desc'] ?? $card['oracle_text'] ?? '' }}">

                        <img src="{{ $card['image_uris']['normal'] ?? $card['image'] ?? Vite::asset('resources/img/decktrove-logo.png') }}"
                            alt="{{ $card['name'] ?? 'Unknown' }}"
                            class="w-full h-full object-cover rounded card">

                        <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 
                            group-hover:opacity-100 transition-opacity rounded p-1 
                            flex flex-col justify-center items-center text-center">
                            <h3 class="font-bold text-[10px] leading-snug">{{ $card['name'] ?? 'Unknown' }}</h3>
                            <p class="text-[8px] mt-1 pointer-events-none">{{ $card['type'] ?? $card['card_type'] ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="p-3 bg-white rounded shadow deckContainer">
                <h2 class="font-bold text-lg mb-2 bg-white">
                    Side Deck (<span id="sideCount">{{ count($deckCards['side']) }}</span>)
                </h2>
                <div id="side" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px]">
                    @foreach($deckCards['side'] as $card)
                    <div class="relative w-20 h-28 mb-2 mx-auto group card-wrapper cursor-pointer"
                        draggable="true"
                        data-card-id="{{ $card['id'] }}"
                        data-card-name="{{ $card['name'] ?? '' }}"
                        data-card-image="{{ $card['image_uris']['normal'] ?? $card['image'] ?? '' }}"
                        data-card-type="{{ $card['type'] ?? $card['card_type'] ?? '' }}"
                        data-card-race="{{ $card['race'] ?? '' }}"
                        data-card-desc="{{ $card['desc'] ?? $card['oracle_text'] ?? '' }}">

                        <img src="{{ $card['image_uris']['normal'] ?? $card['image'] ?? Vite::asset('resources/img/decktrove-logo.png') }}"
                            alt="{{ $card['name'] ?? 'Unknown' }}"
                            class="w-full h-full object-cover rounded card">

                        <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 
                            group-hover:opacity-100 transition-opacity rounded p-1 
                            flex flex-col justify-center items-center text-center">
                            <h3 class="font-bold text-[10px] leading-snug">{{ $card['name'] ?? 'Unknown' }}</h3>
                            <p class="text-[8px] mt-1 pointer-events-none">{{ $card['type'] ?? $card['card_type'] ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
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

            @include("decks.$game.cards-view", ['cards' => $cards])

            <div id="loadingSpinner" class="text-center my-4 hidden">
                <p>Loading more cards...</p>
            </div>

            <form id="saveForm" action="{{ route('decks.update', $deck->id) }}" method="POST" class="space-y-10">
                @csrf
                @method('PATCH')

                <input type="hidden" name="is_public" id="isPublicHidden" value="0">
                <input type="hidden" name="deck_title" id="deckTitle">
                <input type="hidden" name="deck_description" id="deckDescription">
                <input type="hidden" name="cards" id="cards">
                <input type="hidden" name="image" id="deckImage">

                <button type="submit" class="mt-4 w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700">
                    Edit Deck
                </button>
            </form>

        </div>
    </div>
</x-layout>