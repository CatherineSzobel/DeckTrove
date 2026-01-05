<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @foreach($cards as $card)
    @php
    // Image
    $image = ($seriesConfig['image'] ?? fn($c) => '')($card);

    // ATK / DEF or Power / Toughness
    [$atkField, $defField] = $seriesConfig['atk_def'] ?? [null, null];
    $atk = $atkField ? data_get($card, $atkField) : null;
    $def = $defField ? data_get($card, $defField) : null;

    // Description
    $desc = isset($card['card_faces']) && is_array($card['card_faces']) && count($card['card_faces']) > 0
    ? ($seriesConfig['colorless_description'] ?? fn($c) => '')($card)
    : ($seriesConfig['description'] ?? fn($c) => '')($card);

    $type = isset($card['card_faces']) && is_array($card['card_faces']) && count($card['card_faces']) > 0
    ? ($seriesConfig['colorless_type'] ?? fn($c) => '')($card)
    : ($seriesConfig['type_field'] ?? fn($c) => '')($card);

    // Set + Rarity
    $setName = data_get($card, $seriesConfig['set_field'] ?? '') ?? 'No Set';
    $rarityRaw = strtolower(data_get($card, $seriesConfig['rarity_field'] ?? '') ?? 'unknown');
    $rarityColor = $seriesConfig['rarity_colors'][$rarityRaw] ?? 'text-gray-500';

    // Price
    $price = data_get($card, $seriesConfig['price_field'] ?? '') ?: 'N/A';
    @endphp

    <div class="rounded-xl bg-white shadow hover:shadow-lg transition-transform transform hover:scale-105 flex flex-col h-full overflow-hidden">
        {{-- Link & Image --}}
        <a href="{{ url(($seriesConfig['link_prefix'] . '/card/' ?? '/') . ($card['id'] ?? '')) }}" class="relative group">
            <img src="{{ $image }}" alt="{{ $card['name'] ?? 'Card Image' }}"
                class="w-full h-64 object-cover rounded-t-lg transition-transform duration-300 group-hover:scale-105 object-top">
        </a>

        <div class="p-4 flex flex-col flex-1">
            {{-- Name --}}
            <h3 class="text-lg font-semibold mb-2 text-gray-800">{{ $card['name'] ?? 'Unknown Card' }}</h3>

            {{-- Type / Subtype --}}
            <div class="flex justify-between items-center mb-2 text-sm text-gray-600">
                <span>{{ ucfirst($type) }}</span>
                <span class="text-gray-500">{{ data_get($card, $seriesConfig['subtype_field'] ?? '', '-') }}</span>
            </div>

            {{-- ATK / DEF or Power / Toughness --}}
            @if($atk !== null || $def !== null)
            <div class="text-sm font-medium text-gray-700 mb-2">
                {{ $atk ?? '-' }}/{{ $def ?? '-' }}
            </div>
            @endif

            {{-- Description --}}
            @if($desc)
            <div class="text-sm text-gray-700 mb-3 leading-relaxed">
                {!! nl2br(e($desc)) !!}
            </div>
            @endif

            {{-- Set + Rarity --}}
            <div class="flex justify-between items-center text-xs text-gray-500 mt-auto mb-2">
                <span>{{ $setName }}</span>
                <span class="{{ $rarityColor }}">{{ ucfirst($rarityRaw) }}</span>
            </div>

            {{-- Price --}}
            <div class="mt-2 font-semibold text-green-600 text-right">
                ${{ $price }}
            </div>
        </div>
    </div>
    @endforeach
</div>