<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-2">
    @foreach($cards as $card)
    <div class="border rounded-lg shadow p-4 flex flex-col h-full bg-white">

        {{-- Image --}}
        <a href="{{ url('/yugioh/card/' . $card['id']) }}">
            <img
                src="{{ $card['card_images'][0]['image_url'] ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                alt="{{ $card['name'] }}"
                class="max-w-full max-h-64 w-auto h-auto object-contain rounded mb-4">
        </a>

        {{-- Name --}}
        <h3 class="text-lg font-bold mb-2">{{ $card['name'] }}</h3>

        {{-- Type Line --}}
        <div class="flex justify-between items-center mb-2">
            <div class="text-sm text-gray-600">
                {{ $card['type'] ?? 'Unknown' }}
            </div>
            <div class="text-sm text-gray-600 text-right">
                {{ $card['race'] ?? 'Unknown' }}
            </div>
        </div>
        {{-- ATK/DEF --}}
        @if(isset($card['atk']) || isset($card['def']))
        <div class="text-sm font-medium text-gray-800 mb-2">
            ATK: {{ $card['atk'] ?? '-' }} / DEF: {{ $card['def'] ?? '-' }}
        </div>
        @endif
        {{-- Description --}}
        @if(!empty($card['desc']))
        <div class="text-sm text-gray-700 mb-3 leading-relaxed">
            {!! nl2br(e($card['desc'])) !!}
        </div>
        @endif

        {{-- Sets + pseudo rarity --}}
        <div class="flex justify-between text-xs text-gray-500 mb-2 mt-auto">

            {{-- First set name --}}
            <span>
                {{ $card['card_sets'][0]['set_name'] ?? 'No Set' }}
            </span>

            {{-- Rarity color mapping --}}
            @php
            $rarity = strtolower($card['card_sets'][0]['set_rarity'] ?? '');
            $rarityColor = match(true) {
            str_contains($rarity, 'common') => 'gray-600',
            str_contains($rarity, 'rare') && !str_contains($rarity, 'super') && !str_contains($rarity,'ultra') => 'yellow-600',
            str_contains($rarity, 'super') => 'green-700',
            str_contains($rarity, 'ultra') => 'orange-600',
            str_contains($rarity, 'secret') => 'purple-600',
            default => 'gray-500'
            };
            @endphp

            <span class="text-{{ $rarityColor }}">
                {{ $card['card_sets'][0]['set_rarity'] ?? 'Unknown' }}
            </span>
        </div>

        {{-- Price --}}
        <div class="text-sm font-semibold text-green-600 pt-2 border-t border-gray-200">
            ${{ $card['card_prices'][0]['cardmarket_price'] ?? 'N/A' }}
        </div>

    </div>
    @endforeach
</div>