@php

@endphp

<x-layout :js="['resources/js/mydecks.js']">
    <div class="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 p-6">
        <div class="max-w-7xl mx-auto">

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Main Content Area (Left - 3 columns) -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Your Decks Section -->
                    <div class="bg-slate-800 rounded-lg shadow-lg p-6 border border-slate-700">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-white">Your Decks</h2>
                                <a href="{{ route('decks') }}" class="rounded-lg px-4 py-2 inline-block mt-2 text-sm text-white bg-blue-500 hover:bg-blue-600 transition">Go to your decks</a>
                            </div>

                            <!-- TCG Dropdown -->
                            <select id="tcg-filter" class="bg-slate-700 text-white rounded px-4 py-2 border border-slate-600 hover:border-amber-400 transition">
                                <option value="all">All TCGs</option>
                                <option value="yugioh">Yu-Gi-Oh!</option>
                                <option value="magic">Magic: The Gathering</option>
                            </select>
                        </div>

                        <!-- Decks Grid -->
                        @if($recentDecks->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($recentDecks as $deck)
                            <a href="{{ route('decks.show', $deck->id) }}" class="group">
                                <div class="bg-slate-700 rounded-lg p-4 border border-slate-600 hover:border-amber-400 transition transform hover:scale-105">
                                    <div class="bg-gradient-to-br from-slate-600 to-slate-800 rounded h-32 flex items-center justify-center mb-3 overflow-hidden">
                                        @if($deck->image_url)
                                        <img src="{{ $deck->image_url }}" alt="{{ $deck->name }}" class="w-full h-full object-cover">
                                        @else
                                        <div class="text-slate-400 text-center">
                                            <span class="text-3xl">🎴</span>
                                        </div>
                                        @endif
                                    </div>
                                    <h3 class="text-white font-semibold truncate group-hover:text-amber-400 transition">{{ $deck->name }}</h3>
                                    <p class="text-slate-400 text-sm mt-1">{{ ucfirst($deck->series) }}</p>
                                    <p class="text-slate-500 text-xs mt-2">{{ $deck->cards->count() }} cards</p>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8">
                            <p class="text-slate-400 mb-4">No decks yet. Create your first one!</p>
                            <a href="{{ route('yugioh.deck.builder') }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-6 py-2 rounded transition">
                                Create Deck
                            </a>
                        </div>
                        @endif
                    </div>

                    <!-- Card Carousel Section -->
                    <div class="bg-slate-800 rounded-lg shadow-lg p-6 border border-slate-700">
                        <h2 class="text-2xl font-bold text-white mb-4">Featured Cards</h2>
                        <div class="overflow-hidden">
                            <div class="marquee flex gap-4 py-4">
                                @if (isset($randomCards) && count($randomCards) > 0)
                                @foreach($randomCards as $card)
                                @php
                                $series = data_get($card, 'game') ?: (isset($card['card_images']) ? 'yugioh' : 'magic');
                                $imgUrl = null;

                                if (isset($card['card_images'][0]['image_url_small'])) {
                                // Yu-Gi-Oh
                                $imgUrl = $card['card_images'][0]['image_url_small'];
                                $series = 'yugioh';
                                } elseif (isset($card['image_uris']['small'])) {
                                // Magic
                                $imgUrl = $card['image_uris']['small'];
                                $series = 'magic';
                                }
                                $indexLink = url('/' . $series . '/card/' . $card['id']);
                                @endphp
                                <a href="{{ $indexLink }}" class="flex-shrink-0">
                                    <div class="h-48 w-32 rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition transform hover:scale-110 cursor-pointer">
                                        @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $card['name'] ?? 'Card' }}" class="w-full h-full object-cover">
                                        @else
                                        <div class="w-full h-full bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-slate-400">
                                            <span class="text-2xl">🎴</span>
                                        </div>
                                        @endif
                                    </div>
                                </a>
                                @endforeach
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (Right - 1 column) -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Quick Stats -->
                    <div class="bg-slate-800 rounded-lg shadow-lg p-6 border border-slate-700">
                        <h3 class="text-lg font-bold text-white mb-4">Quick Stats</h3>
                        <div class="space-y-3">
                            <div class="bg-slate-700 rounded p-3">
                                <p class="text-slate-400 text-sm">Total decks</p>
                                <p class="text-amber-400 font-bold text-2xl">{{ isset($totalDecks) ? $totalDecks : 0 }}</p>
                            </div>
                            <div class="bg-slate-700 rounded p-3">
                                <p class="text-slate-400 text-sm">Yu-Gi-Oh! Decks</p>
                                <p class="text-blue-400 font-bold text-2xl">{{ isset($yugiohDecks) ? $yugiohDecks  : 0 }}</p>
                            </div>
                            <div class="bg-slate-700 rounded p-3">
                                <p class="text-slate-400 text-sm">Magic Decks</p>
                                <p class="text-purple-400 font-bold text-2xl">{{ isset($magicDecks) ? $magicDecks : 0}}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="bg-slate-800 rounded-lg shadow-lg p-6 border border-slate-700">
                        <h3 class="text-lg font-bold text-white mb-4">Quick Links</h3>
                        <div class="space-y-2">
                            <a href="{{ route('yugioh.cards.index') }}" class="block bg-slate-700 hover:bg-blue-600 text-white rounded px-4 py-2 transition text-center font-semibold">
                                Yu-Gi-Oh! Cards
                            </a>
                            <a href="{{ route('magic.cards.index') }}" class="block bg-slate-700 hover:bg-purple-600 text-white rounded px-4 py-2 transition text-center font-semibold">
                                Magic Cards
                            </a>
                            <a href="{{ route('yugioh.deck.builder') }}" class="block bg-amber-500 hover:bg-amber-600 text-slate-900 rounded px-4 py-2 transition text-center font-semibold">
                                Build Deck
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>