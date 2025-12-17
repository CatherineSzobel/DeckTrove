<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @foreach($cards as $card)
    <div class="rounded-xl bg-white shadow hover:shadow-lg transition-transform transform hover:scale-105 flex flex-col h-full overflow-hidden">
        <a href="{{ url('/magic/card/' . $card['id']) }}" class="relative group">
            <img src="{{ $card['image_uris']['normal'] ?? $card['card_faces'][0]['image_uris']['normal'] ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                 alt="{{ $card['name'] }}"
                 class="w-full h-64 object-cover rounded-t-lg transition-transform duration-300 group-hover:scale-105">
            
            <!-- Optional hover overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-20 opacity-0 group-hover:opacity-100 transition-opacity rounded-t-lg"></div>
        </a>

        <div class="p-4 flex flex-col flex-1">
            <h3 class="text-lg font-semibold mb-2 text-gray-800">{{ $card['name'] }}</h3>

            <div class="flex justify-between items-center mb-2 text-sm text-gray-600">
                <span>{{ $card['mana_cost'] ?? '' }}</span>
                <span class="text-gray-500">{{ $card['type_line'] }}</span>
            </div>

            @if(isset($card['power']) && isset($card['toughness']))
            <div class="text-sm font-medium text-gray-700 mb-2">
                {{ $card['power'] }}/{{ $card['toughness'] }}
            </div>
            @endif

            @if(!empty($card['oracle_text']))
            <div class="text-sm text-gray-700 mb-3 leading-relaxed">
                {!! nl2br(e($card['oracle_text'])) !!}
            </div>
            @endif

            <div class="flex justify-between items-center text-xs text-gray-500 mt-auto mb-2">
                <span>{{ $card['set_name'] }}</span>
                <span class="{{
                        match(strtolower($card['rarity'] ?? '')) {
                            'common' => 'text-gray-600',
                            'uncommon' => 'text-green-700',
                            'rare' => 'text-yellow-600',
                            'mythic' => 'text-orange-600',
                            'special' => 'text-purple-600',
                            'bonus' => 'text-blue-600',
                            default => 'text-gray-500'
                        }
                    }}">
                    {{ ucfirst($card['rarity']) }}
                </span>
            </div>

            <div class="mt-2 font-semibold text-green-600 text-right">
                ${{ $card['prices']['usd'] ?? 'N/A' }}
            </div>
        </div>
    </div>
    @endforeach
</div>
