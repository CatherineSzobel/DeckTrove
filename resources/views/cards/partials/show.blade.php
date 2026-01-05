@php
// Series config
$seriesConfig = config("series.$series");

// Card name
$cardName = $card['name'] ?? $card['card_name'] ?? 'Unknown Card';

// Card image (use seriesConfig image logic if exists)
$cardImage = ($seriesConfig['image'] ?? fn($c) => null)($card);

// Description
$desc = ($seriesConfig['description'] ?? fn($c) => 'No description available')($card);

// ATK / DEF or Power / Toughness
[$atkField, $defField] = $seriesConfig['atk_def'] ?? [null, null];
$atk = $atkField ? data_get($card, $atkField) : null;
$def = $defField ? data_get($card, $defField) : null;

// Type / Subtype
$type = isset($card['card_faces']) && is_array($card['card_faces']) && count($card['card_faces']) > 0
? ($seriesConfig['colorless_type'] ?? fn($c) => '')($card)
: ($seriesConfig['type_field'] ?? fn($c) => '')($card);
$subtype = data_get($card, $seriesConfig['subtype_field'] ?? '') ?? '';

// Price
$price = data_get($card, $seriesConfig['price_field'] ?? '') ?? 'N/A';

// Rarity
$rarityRaw = strtolower(data_get($card, $seriesConfig['rarity_field'] ?? ''));
$rarityColor = $seriesConfig['rarity_colors'][$rarityRaw] ?? 'text-gray-500';

// Printings / sets
$printSets = [];

if ($series === 'magic') {
// Magic cards
$printSets[] = [
'set_code' => $card['set'] ?? '',
'set_name' => $card['set_name'] ?? 'Unknown',
];
} elseif ($series === 'yugioh') {
// Yu-Gi-Oh cards: array of card_sets
foreach ($card['card_sets'] ?? [] as $set) {
$printSets[] = [
'set_code' => $set['set_code'] ?? '',
'set_name' => $set['set_name'] ?? 'Unknown',
];
}
}
@endphp

<div class="flex flex-col md:flex-row md:items-start gap-6 md:gap-8 text-black">

    <!-- LEFT: Card Image -->
    <div class="md:w-1/2 flex-shrink-0 flex items-center justify-center md:flex-1 flex-col">

        @if($cardImage)
        <img src="{{ $cardImage }}"
            alt="{{ $cardName }}"
            class="w-84 max-w-md rounded-lg shadow-lg bg-black object-contain">
        @else
        <div class="w-full max-w-xs h-56 rounded-lg bg-gray-800 flex items-center justify-center">
            <span class="text-slate-400">No image available</span>
        </div>
        @endif

        {{-- Transform button only for Magic --}}
        @if($series === 'magic')
        <button id="toggleImageButton"
            class="mt-4 px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
            onclick="toggleCardImageSize()">
            Transform
        </button>
        @endif

    </div>

    <!-- RIGHT: Card Info -->
    <div class="md:flex-1">

        <h1 class="text-2xl font-bold mb-2 text-black">{{ $cardName }}</h1>

        <div class="text-sm text-slate-800 mb-3">
            <span class="mr-2 text-black">
                Type: <strong>{{ $type }}</strong>
                @if($subtype)
                — <span>{{ $subtype }}</span>
                @endif
            </span>
        </div>

        <div class="flex flex-wrap gap-3 mb-4">
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">
                Price: {{ $price }}
            </div>

            @if($series === 'magic' && data_get($card,'mana_cost'))
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">
                Mana: {{ data_get($card,'mana_cost') }}
            </div>
            @endif

            @if($rarityRaw)
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded {{ $rarityColor }}">
                Rarity: {{ ucfirst($rarityRaw) }}
            </div>
            @endif

            @if($atk !== null && $def !== null)
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">
                {{ $series === 'magic' ? 'Power' : 'ATK' }}: {{ $atk }} |
                {{ $series === 'magic' ? 'Toughness' : 'DEF' }}: {{ $def }}
            </div>
            @endif
        </div>

        <div class="prose prose-invert text-black mb-4">
            {!! nl2br(e($desc)) !!}
        </div>

        <div class="mb-4">
            <h3 class="text-lg font-semibold mb-2 text-black">Printings</h3>
            <x-print-list :sets="$printSets"
                id="prints-{{ $card['id'] ?? 'default' }}"
                :tcg-game="$series" />

        </div>

        @if(!empty($setCards) && $setCards->count())
        <div class="mt-6 border-slate-700 p-6">
            <h3 class="text-lg text-black font-semibold mb-3">More from this set</h3>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                @foreach($setCards as $setCard)
                @php
                $setCardName = $setCard['name'] ?? 'Unknown Card';
                $setCardImage = ($seriesConfig['image'] ?? fn($c) => null)($setCard);
                $link = url("/{$series}/card/".($setCard['id'] ?? ''));
                @endphp

                <a href="{{ $link }}" class="hover:scale-105 transform transition block" aria-label="{{ $setCardName }}">
                    <div class="aspect-[3/4] w-full rounded shadow bg-black">
                        @if($setCardImage)
                        <img src="{{ $setCardImage }}"
                            alt="{{ $setCardName }}"
                            loading="lazy"
                            class="w-full h-full object-contain rounded">
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-800 rounded">
                            <span class="text-slate-400">No image</span>
                        </div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mt-6 flex gap-3">
            <a href="{{ url("/{$series}/cards") }}" class="inline-block bg-slate-700 hover:bg-slate-600 text-white font-bold px-4 py-2 rounded">
                Back to Database
            </a>
            <a href="{{ url("/{$series}/deck-builder") }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded font-bold">
                Go to Deckbuilder
            </a>
        </div>
    </div>
</div>