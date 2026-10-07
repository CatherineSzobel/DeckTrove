<div class="py-6 flex flex-col md:flex-row items-start md:items-center gap-6">
    @if($pack->image())
    <img src="{{ $pack->image() }}"
        alt="{{ $pack->name() }}"
        class="w-20 md:w-48 h-auto border rounded shadow object-cover" />
    @endif

    <div>
        <h1 class="text-3xl font-bold mb-2">{{ $pack->name() }}</h1>

        @if($pack->code())
        <p class="mb-1">Code: {{ strtoupper($pack->code()) }}</p>
        @endif

        @if($pack->type())
        <p class="mb-1">Type: {{ ucfirst(str_replace('_', ' ', $pack->type())) }}</p>
        @endif

        @if($pack->cardCount())
        <p class="mb-1">Card Count: {{ $pack->cardCount() }}</p>
        @endif

        @if($pack->release())
        <p class="mb-1">Released: {{ $pack->release() }}</p>
        @endif
    </div>
</div>

<div class="mt-4 mb-4 flex flex-col md:flex-row gap-4">
    <div class="w-full md:w-1/2">
        <input id="card-filter"
            type="search"
            placeholder="Filter cards by name..."
            class="w-full bg-slate-700 text-white px-4 py-2 rounded border border-slate-600 focus:outline-none" />
    </div>

    <div class="w-full md:w-1/3">
        <select id="rarity-filter" class="w-full bg-slate-700 text-white px-4 py-2 rounded border border-slate-600 focus:outline-none">
            <option value="">All Rarities</option>
            @foreach($cardCollection->rarities() as $rarity)
            <option value="{{ strtolower($rarity) }}">
                {{ ucfirst($rarity) }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="w-full md:w-1/5">
        <a href="{{ route('packs.index', $series) }}"
            class="inline-block bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded">
            Back to Packs
        </a>
    </div>
</div>

<div id="cardsGrid" class="mt-6 max-h-[600px] overflow-y-auto">
    @if($cards->count())
    <div id="cards-grid"
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

        @foreach($cards as $card)

        <div class="card-item bg-slate-800 rounded-lg border border-slate-700 p-3 group"
            data-name="{{ strtolower($card->name()) }}"
            data-rarity="{{ strtolower($card->rarity() ?? '') }}">

            <a href="{{ $card->link() }}">
                <div class="relative h-44 w-full overflow-hidden rounded mb-3">
                    @if($card->image())

                    <img src="{{ $card->image() }}"
                        alt="{{ $card->name() }}"
                        class="w-full h-full object-cover">

                    @else
                    <div class="w-full h-full bg-slate-700 flex items-center justify-center text-slate-400">
                        No Image
                    </div>
                    @endif

                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity
                                    flex items-center justify-center p-4">
                        <div class="text-sm text-white text-center">
                            <div class="font-semibold mb-1">{{ $card->name() }}</div>

                            @if($card->type())
                            <div class="text-slate-200">Type: {{ $card->type() }}</div>
                            @endif

                            @if($card->rarity())
                            <div class="text-slate-200 mt-1">Rarity: {{ ucfirst($card->rarity()) }}</div>
                            @endif

                            @if($card->hasStats())
                            <p class="text-[10px] mt-1">
                                {{ $card->statLine() }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
            <div>
                <a href="{{ $card->link() }}"
                    class="text-white font-semibold block truncate">
                    {{ $card->name()  }}
                </a>

                @if($card->type())
                <p class="text-slate-400 text-sm mt-1">{{ $card->type() }}</p>
                @endif

                @if($card->rarity())
                <p class="text-slate-400 text-sm mt-1">{{ ucfirst($card->rarity()) }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="py-6 text-center text-slate-400">
        No cards found in this pack.
    </div>
    @endif
</div>