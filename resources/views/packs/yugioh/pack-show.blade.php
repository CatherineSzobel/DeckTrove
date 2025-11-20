 {{-- Pack Info --}}
 <div class="py-6 flex flex-col md:flex-row items-start md:items-center gap-6">
     <img src="{{ $pack->set_image }}" alt="{{ $pack->set_name }}" class="w-48 h-auto border rounded shadow">
     <div>
         <h1 class="text-3xl font-bold mb-2">{{ $pack->set_name }}</h1>
         <p class="mb-1">Code: {{ $pack->set_code }}</p>
         <p class="mb-1">Number of cards: {{ $pack->num_of_cards }}</p>
         <p class="mb-1">Release date: {{ $pack->tcg_date }}</p>
     </div>
 </div>

 {{-- Cards Table --}}
 <div class="overflow-x-auto mt-6">
     <table class="w-full border-collapse border border-gray-300 text-sm">
         <thead class="bg-gray-100">
             <tr>
                 <th class="p-2 border">Image</th>
                 <th class="p-2 border">Name</th>
                 <th class="p-2 border">Type</th>
                 <th class="p-2 border">Rarity</th>
             </tr>
         </thead>
         <tbody>
             @forelse($cards as $card)
             <tr class="hover:bg-gray-50">
                 <td class="p-2 border">
                     <img src="{{ $card['card_images'][0]['image_url_small'] ?? '' }}" alt="{{ $card['name'] }}" class="w-20 h-auto">
                 </td>
                 <td class="p-2 border">{{ $card['name'] ?? 'Unknown' }}</td>
                 <td class="p-2 border">{{ $card['type'] ?? 'Unknown' }}</td>
                 <td class="p-2 border">
                     @if(!empty($card['card_sets'][0]['set_rarity']))
                     {{ $card['card_sets'][0]['set_rarity'] }}
                     @else
                     Unknown
                     @endif
                 </td>
             </tr>
             @empty
             <tr>
                 <td class="p-2 border text-center" colspan="4">No cards found in this pack.</td>
             </tr>
             @endforelse
         </tbody>
     </table>
 </div>