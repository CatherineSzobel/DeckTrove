<x-layout>
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-black">My Decks</h1>
                <p class="text-sm text-slate-400 mt-1">Manage and preview all your decks</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-sm text-slate-600 mr-2">Total decks</div>
                <div class="bg-amber-400 text-slate-900 font-semibold px-3 py-1 rounded-md">
                    {{ $decks->count() }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
            <div class="lg:col-span-3">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3 w-full md:w-2/3">
                        <input id="deckSearch" type="search" placeholder="Search decks by name or description..."
                            class="w-full px-4 py-2 rounded-md bg-slate-800 border border-slate-700 text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-400" />
                    </div>

                    <div class="flex items-center gap-3">
                        <select id="seriesFilter"
                            class="bg-slate-800 text-slate-200 px-3 py-2 rounded-md border border-slate-700 focus:outline-none">
                            <option value="all">All</option>
                            <option value="yugioh">Yu-Gi-Oh!</option>
                            <option value="magic">Magic</option>
                        </select>
                        <a href="{{ route('yugioh.deck.builder') }}"
                            class="hidden md:inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-4 py-2 rounded-md">Build Deck</a>
                    </div>
                </div>

                @if($decks->isEmpty())
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-8 text-center text-slate-400">
                    <p class="mb-4">You don't have any decks yet.</p>
                    <a href="{{ route('yugioh.deck.builder') }}"
                        class="inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold px-5 py-2 rounded-md">Create your first deck</a>
                </div>
                @else
                <div id="decksGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($decks as $deck)
                    <div class="deck-card bg-slate-800 border border-slate-700 rounded-lg overflow-hidden shadow-sm transition hover:shadow-lg" data-series="{{ strtolower($deck->series ?? '') }}">
                        <div class="relative h-40 bg-gradient-to-br from-slate-700 to-slate-900 overflow-hidden">
                            @if($deck->image)
                            <img src="{{ $deck->image }}" alt="{{ $deck->name }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-slate-500 text-4xl">🎴</div>
                            @endif
                            <div class="absolute left-3 top-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-black/50 text-white">{{ ucfirst($deck->game ?? 'unknown') }}</span>
                            </div>
                            <div class="absolute right-3 top-3">
                                <form action="{{ route('decks.destroy', $deck->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="text-red-500 font-bold"
                                        onclick="return confirm('Delete this deck?')">
                                        X
                                    </button>
                                </form>

                            </div>
                        </div>

                        <div class="p-4">
                            <a href="{{ route('decks.show', $deck->id) }}" class="block">
                                <h3 class="text-lg font-semibold text-white truncate">{{ $deck->name }}</h3>
                            </a>
                            <p class="text-sm text-slate-400 mt-2 line-clamp-3">{{ $deck->description ?? 'No description' }}</p>
                            <span class="text-sm text-slate-400 mt-2 line-clamp-3">{{ $deck->is_public ? 'Public' : 'Private' }}</span>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-xs text-slate-400">
                                    <span class="font-semibold text-amber-400">
                                        {{ $deck->cards->sum('pivot.count') }}
                                    </span>
                                    <span class="ml-1">cards</span>
                                </div>


                                <div class="flex items-center gap-2">
                                    <a href="{{ route('decks.show', $deck->id) }}" class="text-sm bg-slate-700 hover:bg-slate-600 text-white px-3 py-1 rounded-md">View</a>
                                    <a href="{{ route('decks.edit', $deck->id) }}" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md">Edit</a>
                                </div>
                            </div>

                            <div class="mt-3 text-xs text-slate-500">
                                <span>Created {{ $deck->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(method_exists($decks, 'links'))
                <div class="mt-6">{{ $decks->links() }}</div>
                @endif
                @endif
            </div>

            <aside class="lg:col-span-1 space-y-6">
                <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
                    <h4 class="text-sm font-semibold text-slate-300 mb-3">Stats</h4>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between bg-slate-700 p-3 rounded">
                            <div class="text-sm text-slate-300">All decks</div>
                            <div class="font-semibold text-amber-400">{{ $decks->count() }}</div>
                        </div>
                        <div class="flex items-center justify-between bg-slate-700 p-3 rounded">
                            <div class="text-sm text-slate-300">Yu-Gi-Oh! decks</div>
                            <div class="font-semibold text-blue-400">{{ $decks->where('game', 'yugioh')->count() }}</div>
                        </div>
                        <div class="flex items-center justify-between bg-slate-700 p-3 rounded">
                            <div class="text-sm text-slate-300">Magic decks</div>
                            <div class="font-semibold text-purple-400">{{ $decks->where('game', 'magic')->count() }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
                    <h4 class="text-sm font-semibold text-slate-300 mb-3">Quick Links</h4>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('yugioh.cards.index') }}" class="px-3 py-2 rounded bg-slate-700 hover:bg-blue-600 text-white text-center">Yu-Gi-Oh! Cards</a>
                        <a href="{{ route('magic.cards.index') }}" class="px-3 py-2 rounded bg-slate-700 hover:bg-purple-600 text-white text-center">Magic Cards</a>
                    </div>
                </div>
            </aside>
        </div>

    </div>
</x-layout>