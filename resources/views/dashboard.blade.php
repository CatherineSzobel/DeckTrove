<x-layout :js="['resources/js/moving-carousel.js']" :css="['resources/css/dashboard.css']">
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 p-4 md:p-8 rounded-lg">
        <div class="max-w-7xl mx-auto">

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Main Content Area (Left - 3 columns) -->
                <div class="lg:col-span-3 space-y-6">

                    <!-- Your Decks Section -->
                    <div class="bg-slate-900/50 backdrop-blur-xl rounded-2xl shadow-2xl p-6 md:p-8 border border-slate-800/50 hover:border-amber-500/30 transition-all duration-300">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                            <div>
                                <h2 class="text-3xl font-bold text-white mb-3 flex items-center gap-3">
                                    Your Decks
                                </h2>
                                <a href="{{ route('decks') }}"
                                    class="group inline-flex items-center gap-2 text-sm text-amber-400 hover:text-amber-300 transition-colors">
                                    <span>View all decks</span>
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>

                            <!-- TCG Dropdown -->
                            <form method="GET" action="{{ route('dashboard') }}">
                                <select name="tcg" onchange="this.form.submit()"
                                    class="bg-slate-800/80 text-white rounded-xl px-4 py-2.5 border border-slate-700 hover:border-amber-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all cursor-pointer">
                                    <option value="all" {{ $selectedTcg === 'all' ? 'selected' : '' }}>All TCGs</option>
                                    <option value="yugioh" {{ $selectedTcg === 'yugioh' ? 'selected' : '' }}>Yu-Gi-Oh!</option>
                                    <option value="magic" {{ $selectedTcg === 'magic' ? 'selected' : '' }}>Magic: The Gathering</option>
                                    <option value="pokemon" {{ $selectedTcg === 'pokemon' ? 'selected' : '' }}>Pokemon</option>
                                    <option value="digimon" {{ $selectedTcg === 'digimon' ? 'selected' : '' }}>Digimon</option>
                                </select>
                            </form>
                        </div>

                        <!-- Decks Grid -->
                        @if($recentDecks->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach($recentDecks as $deck)
                            <a href="{{ route('decks.show', $deck->id) }}" class="group">
                                <div class="bg-gradient-to-br from-slate-800/80 to-slate-900/80 backdrop-blur-sm rounded-xl p-4 border border-slate-700/50 hover:border-amber-400/50 transition-all duration-300 transform hover:scale-105 hover:shadow-xl hover:shadow-amber-500/10">
                                    <div class="bg-gradient-to-br from-slate-700 to-slate-900 rounded-lg h-40 flex items-center justify-center mb-3 overflow-hidden relative">
                                        @if($deck->image)
                                        <img src="{{ $deck->image }}" alt="{{ $deck->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" loading="eager">
                                        @else
                                        <div class="text-slate-500 group-hover:text-amber-400 transition-colors">
                                            <span class="text-5xl">No Image</span>
                                        </div>
                                        @endif
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    </div>
                                    <h3 class="text-white font-semibold truncate group-hover:text-amber-400 transition-colors text-lg">
                                        {{ $deck->name }}
                                    </h3>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="text-slate-400 text-sm">{{ ucfirst($deck->game) }}</p>
                                        <span class="bg-slate-700/50 text-amber-400 text-xs font-medium px-2.5 py-1 rounded-full">
                                            {{ $deck->cards->count() }} cards
                                        </span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-16">
                            <p class="text-slate-400 text-lg mb-6">No decks yet. Start building your collection!</p>
                            <a href="{{ route('magic.deck.builder') }}"
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-900 font-semibold px-8 py-3 rounded-xl transition-all transform hover:scale-105 shadow-lg hover:shadow-amber-500/50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Your First Deck
                            </a>
                        </div>
                        @endif
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-slate-900/90 backdrop-blur-md rounded-2xl shadow-lg border border-slate-700/50 p-6 hover:border-amber-500/40 transition-all">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-2xl">📊</span>
                            <h3 class="text-xl font-bold text-white">Quick Stats</h3>
                        </div>

                        <!-- Horizontal stats row -->
                        <div class="flex flex-wrap gap-4 justify-start">
                            @foreach($tcgs as $tcg)
                            <div class="flex-1 min-w-[90px] bg-slate-800/80 p-3 rounded-xl border border-slate-600/30 hover:border-amber-400/50 transition-all flex flex-col items-center text-center">
                                <p class="text-slate-400 text-sm mb-1">{{ $tcg['name'] }}</p>
                                <p class="text-amber-400 font-bold text-2xl">{{ $tcg['count'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Card Carousel Section -->
                    <div class="bg-slate-900/50 backdrop-blur-xl rounded-2xl shadow-2xl p-6 md:p-8 border border-slate-800/50 hover:border-purple-500/30 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-3xl">✨</span>
                            <h2 class="text-3xl font-bold text-white">Featured Cards</h2>
                        </div>
                        <div class="overflow-hidden relative rounded-xl">
                            <div class="marquee">
                                <div class="flex gap-4 py-4">
                                    @foreach($randomCards as $card)
                                    @php
                                    $imgUrl = data_get($card, 'image_uris.small');
                                    $indexLink = url('/magic/card/' . ($card['id'] ?? ''));
                                    @endphp
                                    <a href="{{ $indexLink }}" class="flex-shrink-0 card-link group">
                                        <div class="h-56 w-40 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl hover:shadow-purple-500/30 transition-all transform hover:scale-110 hover:-rotate-2 cursor-pointer border-2 border-slate-700 hover:border-purple-500">
                                            @if($imgUrl)
                                            <img src="{{ $imgUrl }}" alt="{{ $card['name'] ?? 'Card' }}" class="w-full h-full object-cover" loading="lazy">
                                            @else
                                            <div class="w-full h-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 flex items-center justify-center text-slate-500 group-hover:text-purple-400 transition-colors">
                                                <span class="text-4xl">No image</span>
                                            </div>
                                            @endif
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (Right - 1 column) -->
                <div class="lg:col-span-1 flex flex-col gap-6 lg:h-full">
                    <!-- Quick Links -->
                    <div class="bg-slate-900/90 backdrop-blur-md rounded-2xl shadow-lg border border-slate-700/50 p-6 hover:border-purple-500/40 transition-all flex flex-col">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-2xl">🔗</span>
                            <h3 class="text-xl font-bold text-white">Quick Links</h3>
                        </div>
                        <div class="flex flex-col gap-3">
                            <x-dashboard-quick-link class="gap-2"
                                :routes="['#', 'magic.cards.index', 'public-deck', 'magic.deck.builder']" :titles="['Yu-Gi-Oh! Cards', 'Magic Cards', 'Public Decks', 'Deck Builder']"
                                :colors="[
    'bg-slate-800 border border-slate-700 hover:border-purple-500',
    'bg-slate-800 border border-slate-700 hover:border-purple-500',
    'bg-slate-800 border border-slate-700 hover:border-red-500',
    'bg-slate-800 border border-slate-700 hover:border-amber-500'
]" />
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-layout>