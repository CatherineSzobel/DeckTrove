{{-- Deck builder, used both to create a deck ($deck === null) and to edit one. --}}
@php
    $rules = config("series.$game.deck");
    $editing = $deck !== null;
@endphp

<x-layout :title="$editing ? 'Edit '.$deck->name : 'Deck builder'" :js="['resources/js/deck-builder.js']" :css="['resources/css/deckbuilder.css']">
    <div class="relative">
        @guest
        <div class="fixed inset-0 z-[1000] flex items-center justify-center bg-gradient-to-br from-black/70 via-black/60 to-black/70">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8 text-center" role="dialog" aria-labelledby="loginRequiredTitle">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100">
                    <x-heroicon-o-lock-closed class="h-7 w-7 text-blue-600" />
                </div>

                <h2 id="loginRequiredTitle" class="text-2xl font-bold text-gray-900 mb-2">Login Required</h2>
                <p class="text-gray-600 mb-6">Sign in to build decks, save progress, and share your creations.</p>

                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-6 py-2.5 rounded-full bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-6 py-2.5 rounded-full border border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition-colors">Create account</a>
                </div>
            </div>
        </div>
        @endguest

        <div id="deckApp"
            data-game="{{ $game }}"
            data-rules="{{ json_encode($rules) }}"
            data-search-url="{{ route('decks.builder', $game) }}"
            data-next-page="{{ $cards->hasMorePages() ? $cards->currentPage() + 1 : '' }}"
            @class(['grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4', 'pointer-events-none opacity-50' => auth()->guest()])>

            <div class="col-span-1 md:col-span-2">
                @if ($errors->any())
                <ul class="p-2 font-bold text-red-500 text-center" role="alert">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @endif

                <div class="p-3 bg-white rounded shadow" id="deckCoverContainer">
                    <label for="deckTitleInput" class="sr-only">Deck title</label>
                    <input id="deckTitleInput" class="input w-full" maxlength="255" placeholder="Enter deck title..." value="{{ old('deck_title', $deck?->name) }}">
                    <label for="deckDescInput" class="sr-only">Deck description</label>
                    <input id="deckDescInput" class="input w-full mt-2" maxlength="2000" placeholder="Enter deck description..." value="{{ old('deck_description', $deck?->description) }}">

                    <div id="coverDropArea" class="mt-2 p-4 border-2 border-dashed border-gray-400 rounded text-center text-gray-600">
                        Drag a card from your deck here to set it as the deck cover
                    </div>

                    <img id="coverPreview" src="{{ $deck?->image }}" alt="Deck cover" title="Click to remove"
                        @class(['mt-2 w-32 mx-auto rounded shadow cursor-pointer', 'hidden' => ! $deck?->image])>

                    <div class="flex justify-between items-center mt-3">
                        <button type="button" id="resetButton" class="text-sm text-gray-600 hover:text-red-600">Reset</button>

                        <label class="flex items-center gap-2 font-semibold">
                            <input type="checkbox" id="isPublicCheckbox" @checked(old('is_public', $deck?->is_public))>
                            Public
                        </label>
                    </div>
                </div>

                @foreach ($rules['zones'] as $zone => $zoneRules)
                <div @class(['p-3 bg-white rounded shadow deckContainer mt-4', 'deck-active' => $loop->first]) data-zone-container="{{ $zone }}">
                    <h2 class="font-bold text-lg mb-2 bg-white">
                        {{ $zoneRules['label'] }} (<span data-zone-count="{{ $zone }}">0</span>)
                    </h2>
                    <div id="{{ $zone }}" class="dropzone min-h-[150px] grid grid-cols-5 gap-2 max-h-[800px]">
                        @foreach ($sections[$zone] ?? [] as $card)
                        @include('decks.partials.deck-slot', ['card' => $card])
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="space-y-4">
                <div class="flex w-full gap-2">
                    <div class="flex-[4] p-3 bg-white rounded shadow">
                        <h2 class="font-bold text-lg mb-2 bg-white">Card Search</h2>
                        <div id="result-count" class="text-sm text-gray-600" aria-live="polite">
                            @include('cards.partials.result-count')
                        </div>
                        <label for="searchInput" class="sr-only">Search cards</label>
                        <input type="search" id="searchInput" placeholder="Search cards and press Enter..." class="w-full p-2 border rounded" value="{{ request('search') }}">
                    </div>

                    <div class="flex-[1] p-3 bg-white rounded shadow flex flex-col gap-2">
                        <x-filter-button />
                        <button type="button" id="clear-filter-button" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors">Clear</button>
                    </div>
                </div>

                <x-filter-section :series="$game" :options="$options" />

                <div class="p-3 bg-white rounded shadow search-pool max-h-[600px] w-full overflow-y-auto overflow-x-hidden">
                    @include('decks.partials.cards-view')
                </div>

                <div id="loadingSpinner" class="text-center my-4 hidden" aria-live="polite">
                    <p>Loading more cards...</p>
                </div>

                <form id="saveForm" method="POST" class="w-full"
                    action="{{ $editing ? route('decks.update', $deck) : route('decks.store', $game) }}">
                    @csrf
                    @if ($editing)
                    @method('PATCH')
                    @endif

                    <input type="hidden" name="cards" id="cards">
                    <input type="hidden" name="deck_title" id="deckTitle">
                    <input type="hidden" name="deck_description" id="deckDescription">
                    <input type="hidden" name="is_public" id="isPublic" value="0">
                    <input type="hidden" name="image" id="deckImage" value="{{ old('image', $deck?->image) }}">

                    <button type="submit" class="mt-4 w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700 disabled:opacity-50">
                        {{ $editing ? 'Save Changes' : 'Save Deck' }}
                    </button>
                </form>
            </div>

            <div id="deckWarningModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50" role="dialog" aria-labelledby="deckWarningTitle">
                <div class="bg-white rounded p-6 w-96 max-w-full shadow-lg">
                    <h2 id="deckWarningTitle" class="text-lg font-bold mb-4">Deck Too Small</h2>
                    <p class="mb-4">
                        Your main deck has fewer than {{ $rules['zones']['main']['min'] }} cards, so it can't be shared publicly yet.
                        Do you want to save it as a private deck?
                    </p>
                    <div class="flex justify-end gap-2">
                        <button type="button" id="cancelSaveBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                        <button type="button" id="confirmSaveBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save as private</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
