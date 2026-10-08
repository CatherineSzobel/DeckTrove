@php
    $series = config('series');
    $defaultSeries = session('series', array_key_first($series));
    $quickLinks = [
        ...collect($series)->map(fn ($config, $key) => [
            'href' => route('cards.index', $key),
            'title' => $config['label'].' cards',
            'class' => 'bg-slate-800 border border-slate-700 hover:border-purple-500',
        ])->values()->all(),
        ['href' => route('public-deck'), 'title' => 'Public Decks', 'class' => 'bg-slate-800 border border-slate-700 hover:border-red-500'],
        ['href' => route('decks.builder', $defaultSeries), 'title' => 'Deck Builder', 'class' => 'bg-slate-800 border border-slate-700 hover:border-amber-500'],
    ];
@endphp

<x-layout title="Dashboard" :js="['resources/js/moving-carousel.js']" :css="['resources/css/dashboard.css']">
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 p-4 md:p-8 rounded-lg">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-3 space-y-6">
                    <div class="bg-slate-900/50 backdrop-blur-xl rounded-2xl shadow-2xl p-6 md:p-8 border border-slate-800/50 hover:border-amber-500/30 transition-all duration-300">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                            <div>
                                <h2 class="text-3xl font-bold text-white mb-3">Your Decks</h2>
                                <a href="{{ route('decks') }}" class="group inline-flex items-center gap-2 text-sm text-amber-400 hover:text-amber-300 transition-colors">
                                    <span>View all decks</span>
                                    <x-heroicon-m-chevron-right class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                                </a>
                            </div>
                            <form method="GET" action="{{ route('dashboard') }}">
                                <label for="tcgFilter" class="sr-only">Filter decks by series</label>
                                <select id="tcgFilter" name="tcg" data-autosubmit
                                    class="bg-slate-800/80 text-white rounded-xl px-4 py-2.5 border border-slate-700 hover:border-amber-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 transition-all cursor-pointer">
                                    <option value="">All TCGs</option>
                                    @foreach ($series as $key => $config)
                                    <option value="{{ $key }}" @selected($selectedTcg === $key)>{{ $config['label'] }}</option>
                                    @endforeach
                                </select>
                                <noscript><button type="submit" class="ml-2 text-amber-400">Filter</button></noscript>
                            </form>
                        </div>

                        @if ($recentDecks->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                            @foreach ($recentDecks as $deck)
                            <a href="{{ route('decks.show', $deck) }}" class="group">
                                <div class="bg-gradient-to-br from-slate-800/80 to-slate-900/80 backdrop-blur-sm rounded-xl p-4 border border-slate-700/50 hover:border-amber-400/50 transition-all duration-300 transform hover:scale-105 hover:shadow-xl hover:shadow-amber-500/10">
                                    <div class="bg-gradient-to-br from-slate-700 to-slate-900 rounded-lg h-40 flex items-center justify-center mb-3 overflow-hidden relative">
                                        @if ($deck->image)
                                        <img src="{{ $deck->image }}" alt="{{ $deck->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        @else
                                        <span class="text-slate-500 group-hover:text-amber-400 transition-colors text-2xl">No Image</span>
                                        @endif
                                    </div>
                                    <h3 class="text-white font-semibold truncate group-hover:text-amber-400 transition-colors text-lg">{{ $deck->name }}</h3>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="text-slate-400 text-sm">{{ $series[$deck->game]['label'] ?? ucfirst($deck->game) }}</p>
                                        <span class="bg-slate-700/50 text-amber-400 text-xs font-medium px-2.5 py-1 rounded-full">{{ $deck->card_count }} cards</span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-16">
                            <p class="text-slate-400 text-lg mb-6">No decks yet. Start building your collection!</p>
                            <a href="{{ route('decks.builder', $defaultSeries) }}"
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-900 font-semibold px-8 py-3 rounded-xl transition-all transform hover:scale-105 shadow-lg hover:shadow-amber-500/50">
                                <x-heroicon-o-plus class="w-5 h-5" />
                                Create Your First Deck
                            </a>
                        </div>
                        @endif
                    </div>

                    <div class="bg-slate-900/90 backdrop-blur-md rounded-2xl shadow-lg border border-slate-700/50 p-6 hover:border-amber-500/40 transition-all">
                        <h3 class="text-xl font-bold text-white mb-4">Quick Stats</h3>
                        <div class="flex flex-wrap gap-4 justify-start">
                            @foreach ($deckCounts as $key => $count)
                            <div class="flex-1 min-w-[90px] bg-slate-800/80 p-3 rounded-xl border border-slate-600/30 hover:border-amber-400/50 transition-all flex flex-col items-center text-center">
                                <p class="text-slate-400 text-sm mb-1">{{ $key === 'total' ? 'Total' : $series[$key]['label'] }}</p>
                                <p class="text-amber-400 font-bold text-2xl">{{ $count }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if ($randomCards->isNotEmpty())
                    <div class="bg-slate-900/50 backdrop-blur-xl rounded-2xl shadow-2xl p-6 md:p-8 border border-slate-800/50 hover:border-purple-500/30 transition-all duration-300">
                        <h3 class="text-xl font-bold text-white mb-2">Discover random cards</h3>
                        <div class="overflow-hidden relative rounded-xl">
                            <div class="marquee">
                                <div class="flex gap-4 py-4">
                                    @foreach ($randomCards as $card)
                                    <a href="{{ $card->link() }}" target="_blank" class="flex-shrink-0 card-link group">
                                        <div class="h-56 w-40 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl hover:shadow-purple-500/30 transition-all transform hover:scale-110 hover:-rotate-2 cursor-pointer border-2 border-slate-700 hover:border-purple-500">
                                            <img src="{{ $card->imageSmall() }}" alt="{{ $card->name() }}" class="w-full h-full object-cover" loading="lazy">
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="lg:col-span-1 flex flex-col gap-6 lg:h-full">
                    <div class="bg-slate-900/90 backdrop-blur-md rounded-2xl shadow-lg border border-slate-700/50 p-6 hover:border-purple-500/40 transition-all flex flex-col">
                        <h3 class="text-xl font-bold text-white mb-6">Quick Links</h3>
                        <x-dashboard-quick-link class="gap-2" :links="$quickLinks" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
