@php
$packConfig = $seriesConfig['pack'] ?? [];

// Pack fields
$packName = data_get($pack, $packConfig['name'] ?? 'name', 'Unknown Set');
$packCode = data_get($pack, $packConfig['code'] ?? 'code', '-');
$packType = data_get($pack, $packConfig['type'] ?? null);
$packCardCount = data_get($pack, $packConfig['card_count'] ?? 'card_count', '-');
$packRelease = data_get($pack, $packConfig['release_date'] ?? 'release_date', 'Unknown');
$packImage = data_get($pack, $packConfig['image'] ?? 'image', '');
$linkPrefix = $seriesConfig['link_prefix'] ?? '/';
@endphp

<div class="py-6 flex flex-col md:flex-row items-start md:items-center gap-6">
    <img src="{{ $packImage }}" alt="{{ $packName }}" class="w-20 md:w-48 h-auto border rounded shadow object-cover" />
    <div>
        <h1 class="text-3xl font-bold mb-2">{{ $packName }}</h1>
        <p class="mb-1">Code: {{ strtoupper($packCode) }}</p>
        @if(isset($packConfig['type']))
        <p class="mb-1">Type: {{ ucfirst(str_replace('_', ' ', $packType)) }}</p>
        @endif
        <p class="mb-1">Card Count: {{ $packCardCount }}</p>
        <p class="mb-1">Released: {{ $packRelease }}</p>
    </div>
</div>

<div class="mt-4 mb-4 flex flex-col md:flex-row gap-4">
    <div class="w-full md:w-1/2">
        <label for="card-filter" class="sr-only">Filter cards</label>
        <input id="card-filter" type="search" placeholder="Filter cards by name..."
            class="w-full bg-slate-700 text-white px-4 py-2 rounded border border-slate-600 focus:outline-none" />
    </div>

    <div class="w-full md:w-1/3">
        <label for="rarity-filter" class="sr-only">Filter by rarity</label>
        <select id="rarity-filter"
            class="w-full bg-slate-700 text-white px-4 py-2 rounded border border-slate-600 focus:outline-none">
            <option value="">All Rarities</option>

            @php
            $rarities = collect($cards)
            ->map(fn($card) => data_get($card, $seriesConfig['rarity_field'] ?? 'rarity'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
            @endphp

            @foreach($rarities as $r)
            <option value="{{ strtolower($r) }}">{{ ucfirst($r) }}</option>
            @endforeach
        </select>
    </div>

    <div class="w-full md:w-1/5">
        <a href="{{ url($linkPrefix . '/packs') }}" class="inline-block bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded">
            Back to Database
        </a>
    </div>
</div>

<div id="cardsGrid" class="mt-6 max-h-[600px] overflow-y-auto">
    @if(!empty($cards) && count($cards))
    <div id="cards-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($cards as $card)
        @php
        $img = ($seriesConfig['image'] ?? fn($c) => '')($card);
        $name = data_get($card, 'name') ?? 'Unknown';
        $desc = isset($card['card_faces']) && is_array($card['card_faces']) && count($card['card_faces']) > 0
        ? ($seriesConfig['colorless_description'] ?? fn($c) => '')($card)
        : ($seriesConfig['description'] ?? fn($c) => '')($card);

        $type = isset($card['card_faces']) && is_array($card['card_faces']) && count($card['card_faces']) > 0
        ? ($seriesConfig['colorless_type'] ?? fn($c) => '')($card)
        : ($seriesConfig['type_field'] ?? fn($c) => '')($card);
        $rarity = data_get($card, $seriesConfig['rarity_field'] ?? 'rarity') ?? '-';
        [$atkField, $defField] = $seriesConfig['atk_def'] ?? [null, null];
        $atk = $atkField ? data_get($card, $atkField) : null;
        $def = $defField ? data_get($card, $defField) : null;
        @endphp

        <div class="card-item bg-slate-800 rounded-lg border border-slate-700 p-3 group"
            data-name="{{ strtolower($name) }}"
            data-rarity="{{ strtolower($rarity) }}">

            <div class="relative h-44 w-full overflow-hidden rounded mb-3">
                @if($img)
                <img src="{{ $img }}" alt="{{ $name }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-slate-400">No Image</div>
                @endif

                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-4">
                    <div class="text-sm text-white text-center">
                        <div class="font-semibold mb-1">{{ $name }}</div>
                        @if($type)<div class="text-slate-200">Type: {{ $type }}</div>@endif
                        @if($rarity)<div class="text-slate-200 mt-1">Rarity: {{ $rarity }}</div>@endif

                        @if($atk !== null && $def !== null)
                        <p class="text-[10px] mt-1">
                            {{ $series === 'magic' ? 'Power' : 'ATK' }}: {{ $atk }} |
                            {{ $series === 'magic' ? 'Toughness' : 'DEF' }}: {{ $def }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ url($linkPrefix . '/card/' . ($card['id'] ?? '')) }}" class="text-white font-semibold block truncate">{{ $name }}</a>
                @if($type)<p class="text-slate-400 text-sm mt-1">{{ $type }}</p>@endif
                @if($rarity)<p class="text-slate-400 text-sm mt-1">{{ $rarity }}</p>@endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="py-6 text-center text-slate-400">No cards found in this pack.</div>
    @endif
</div>