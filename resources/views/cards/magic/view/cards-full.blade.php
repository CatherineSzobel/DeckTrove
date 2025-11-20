 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
     @foreach($cards as $card)
     <div class="border rounded-lg shadow p-4 flex flex-col h-full bg-white">
         <a href="{{ url('/magic/card/' . $card['id']) }}">
             <img src="{{ $card['image_uris']['normal'] ?? $card['card_faces'][0]['image_uris']['normal'] ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                 alt="{{ $card['name'] }}"
                 class="max-w-full max-h-64 w-auto h-auto object-contain rounded mb-4">
         </a>

         <h3 class="text-lg font-bold mb-2">{{ $card['name'] }}</h3>

         <div class="flex justify-between items-center mb-2">
             <div class="text-sm">
                 {{ $card['mana_cost'] ?? '' }}
             </div>
             <div class="text-sm text-gray-600 text-right">
                 {{ $card['type_line'] }}
             </div>
         </div>

         @if(isset($card['power']) && isset($card['toughness']))
         <div class="text-sm font-medium text-gray-800 mb-2">
             {{ $card['power'] }}/{{ $card['toughness'] }}
         </div>
         @endif
         
         @if(!empty($card['oracle_text']))
         <div class="text-sm text-gray-700 mb-3 leading-relaxed">
             {!! nl2br(e($card['oracle_text'])) !!}
         </div>
         @endif



         <div class="flex justify-between text-xs text-gray-500 mb-2 mt-auto">
             <span>{{ $card['set_name'] }}</span>
             <span class="text-{{ 
                    match(strtolower($card['rarity'] ?? '')) {
                        'common' => 'gray-600',
                        'uncommon' => 'green-800', 
                        'rare' => 'yellow-600',
                        'mythic' => 'orange-600',
                        'special' => 'purple-600',
                        'bonus' => 'blue-600',
                        default => 'gray-500'
                    }
                }}">
                 {{ ucfirst($card['rarity']) }}
             </span>
         </div>

         <div class="text-sm font-semibold text-green-600 pt-2 border-t border-gray-200">
             ${{ $card['prices']['usd'] ?? 'N/A' }}
         </div>
     </div>
     @endforeach
 </div>