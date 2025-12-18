<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @foreach($cards as $card)
    <div class="rounded-xl bg-white shadow hover:shadow-lg transition-transform transform hover:scale-105 flex flex-col h-full overflow-hidden">
        {{-- Image --}}
        <a href="{{ url('/yugioh/card/' . $card['id']) }}" class="relative group">
            <img
                src="{{ $card['card_images'][0]['image_url'] ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                alt="{{ $card['name'] }}"
                class="w-full h-64 object-cover rounded-t-lg transition-transform duration-300 group-hover:scale-105 object-top">
        </a>

        <div class="p-4 flex flex-col flex-1">
            {{-- Name --}}
            <h3 class="text-lg font-semibold mb-2 text-gray-800">{{ $card['name'] }}</h3>

            {{-- Type & Race --}}
            <div class="flex justify-between items-center mb-2 text-sm text-gray-600">
                <span>{{ $card['type'] ?? 'Unknown' }}</span>
                <span>{{ $card['race'] ?? 'Unknown' }}</span>
            </div>

            {{-- ATK / DEF --}}
            @if(isset($card['atk']) || isset($card['def']))
            <div class="text-sm font-medium text-gray-700 mb-2">
                ATK: {{ $card['atk'] ?? '-' }} / DEF: {{ $card['def'] ?? '-' }}
            </div>
            @endif

            {{-- Description --}}
            @if(!empty($card['desc']))
            <div class="text-sm text-gray-700 mb-3 leading-relaxed">
                {!! nl2br(e($card['desc'])) !!}
            </div>
            @endif

            {{-- Set + Rarity --}}
            <div class="flex justify-between items-center text-xs text-gray-500 mt-auto mb-2">
                <span>{{ $card['card_sets'][0]['set_name'] ?? 'No Set' }}</span>
                @php
                $rarity = strtolower($card['card_sets'][0]['set_rarity'] ?? '');
                $rarityColor = match(true) {
                str_contains($rarity, 'common') => 'text-gray-600',
                str_contains($rarity, 'rare') && !str_contains($rarity, 'super') && !str_contains($rarity,'ultra') => 'text-yellow-600',
                str_contains($rarity, 'super') => 'text-green-700',
                str_contains($rarity, 'ultra') => 'text-orange-600',
                str_contains($rarity, 'secret') => 'text-purple-600',
                default => 'text-gray-500'
                };
                @endphp
                <span class="{{ $rarityColor }}">{{ $card['card_sets'][0]['set_rarity'] ?? 'Unknown' }}</span>
            </div>

            {{-- Price --}}
            <div class="mt-2 font-semibold text-green-600 text-right">
                ${{ $card['card_prices'][0]['cardmarket_price'] ?? 'N/A' }}
            </div>
        </div>
    </div>
    @endforeach
</div>