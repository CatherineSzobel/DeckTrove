@php $defaultSeries = session('series', array_key_first(config('series'))); @endphp

<x-layout title="My decks" :js="['resources/js/mydecks.js']">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-black">My Decks</h1>
                <p class="text-sm text-slate-500 mt-1">Manage and preview all your decks</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-sm text-slate-600 mr-2">Total decks</div>
                <div class="bg-amber-400 text-slate-900 font-semibold px-3 py-1 rounded-md">{{ $decks->count() }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
            <div class="lg:col-span-3">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3 w-full md:w-2/3">
                        <input id="deckSearch" type="search" placeholder="Search decks by name or description..." aria-label="Search decks"
                            class="w-full px-4 py-2 rounded-md bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400" />
                    </div>

                    <div class="flex items-center gap-3">
                        <select id="seriesFilter" aria-label="Filter by series"
                            class="bg-slate-800 text-slate-200 px-3 py-2 rounded-md border border-slate-700 focus:outline-none">
                            <option value="all">All</option>
                            @foreach (config('series') as $series => $config)
                            <option value="{{ $series }}">{{ $config['label'] }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('decks.builder', $defaultSeries) }}"
                            class="hidden md:inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-md">Build Deck</a>
                    </div>
                </div>

                @if ($decks->isEmpty())
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-8 text-center text-slate-400">
                    <p class="mb-4">You don't have any decks yet.</p>
                    <a href="{{ route('decks.builder', $defaultSeries) }}"
                        class="inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-5 py-2 rounded-md">Create your first deck</a>
                </div>
                @else
                <div id="decksGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($decks as $deck)
                    <div class="deck-card bg-slate-800 border border-slate-700 rounded-lg overflow-hidden shadow-sm transition hover:shadow-lg"
                        data-series="{{ $deck->game }}" data-search="{{ strtolower($deck->name.' '.$deck->description) }}">
                        <div class="relative h-40 bg-gradient-to-br from-slate-700 to-slate-900 overflow-hidden">
                            @if ($deck->image)
                            <img src="{{ $deck->image }}" alt="{{ $deck->name }}" loading="lazy" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-slate-500 text-4xl" aria-hidden="true">🎴</div>
                            @endif
                            <div class="absolute left-3 top-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-black/50 text-white">{{ config("series.{$deck->game}.label", ucfirst($deck->game)) }}</span>
                            </div>
                            <div class="absolute right-3 top-3">
                                <form action="{{ route('decks.destroy', $deck) }}" method="POST" data-confirm="Delete “{{ $deck->name }}”? This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded bg-black/50 px-2 py-1 text-red-400 font-bold hover:text-red-300" aria-label="Delete {{ $deck->name }}">✕</button>
                                </form>
                            </div>
                        </div>

                        <div class="p-4">
                            <a href="{{ route('decks.show', $deck) }}" class="block">
                                <h3 class="text-lg font-semibold text-white truncate">{{ $deck->name }}</h3>
                            </a>
                            <p class="text-sm text-slate-400 mt-2 line-clamp-3">{{ $deck->description ?: 'No description' }}</p>
                            <span class="text-sm text-slate-400 mt-2 block">{{ $deck->is_public ? 'Public' : 'Private' }}</span>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-xs text-slate-400">
                                    <span class="font-semibold text-amber-400">{{ $deck->card_count }}</span>
                                    <span class="ml-1">cards</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('decks.show', $deck) }}" class="text-sm bg-slate-700 hover:bg-slate-600 text-white px-3 py-1 rounded-md">View</a>
                                    <a href="{{ route('decks.edit', $deck) }}" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md">Edit</a>
                                </div>
                            </div>

                            <div class="mt-3 text-xs text-slate-500">Created {{ $deck->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <p id="noDeckMatches" class="hidden text-center text-slate-500 py-8">No decks match your search.</p>
                @endif
            </div>

            <aside class="lg:col-span-1 space-y-6">
                <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
                    <h2 class="text-sm font-semibold text-slate-300 mb-3">Stats</h2>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between bg-slate-700 p-3 rounded">
                            <div class="text-sm text-slate-300">All decks</div>
                            <div class="font-semibold text-amber-400">{{ $decks->count() }}</div>
                        </div>
                        @foreach (config('series') as $series => $config)
                        <div class="flex items-center justify-between bg-slate-700 p-3 rounded">
                            <div class="text-sm text-slate-300">{{ $config['label'] }} decks</div>
                            <div class="font-semibold text-blue-400">{{ $decks->where('game', $series)->count() }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
                    <h2 class="text-sm font-semibold text-slate-300 mb-3">Quick Links</h2>
                    <div class="flex flex-col gap-2">
                        @foreach (config('series') as $series => $config)
                        <a href="{{ route('cards.index', $series) }}" class="px-3 py-2 rounded bg-slate-700 hover:bg-blue-600 text-white text-center">{{ $config['label'] }} cards</a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-layout>
