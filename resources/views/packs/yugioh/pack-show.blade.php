 {{-- Pack Info --}}
 <div class="py-6 flex flex-col md:flex-row items-start md:items-center gap-6">
     <img src="{{ $pack->set_image ?? '' }}" alt="{{ $pack->set_name ?? 'Card set image' }}" class="w-48 h-auto border rounded shadow object-cover" loading="lazy" />
     <div>
         <h1 class="text-3xl font-bold mb-2">{{ $pack->set_name }}</h1>
         <p class="mb-1">Code: {{ $pack->set_code }}</p>
         <p class="mb-1">Number of cards: {{ $pack->num_of_cards }}</p>
         <p class="mb-1">Release date: {{ $pack->tcg_date }}</p>
     </div>
 </div>

 {{-- Search / Filter --}}

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
             ->pluck('card_sets.0.set_rarity')
             ->filter()
             ->unique()
             ->sort()
             ->values();
             @endphp

             @foreach($rarities as $r)
             <option value="{{ strtolower($r) }}">{{ $r }}</option>
             @endforeach
         </select>
     </div>
     <div class="w-full md:w-1/5">
         <a href="/yugioh/packs" class="inline-block bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded">Back to Database</a>
     </div>
 </div>

 {{-- Cards Grid --}}
 <div class="mt-6 max-h-[600px] overflow-y-auto">
     @if(!empty($cards) && count($cards))
     <div id="cards-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 ">
         @foreach($cards as $card)
         @php
         $img = data_get($card, 'card_images.0.image_url_small') ?: '';
         $name = $card['name'] ?? 'Unknown';
         $type = $card['type'] ?? '-';
         $rarity = data_get($card, 'card_sets.0.set_rarity') ?: 'Unknown';
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
                         <div class="text-slate-200">Type: {{ $type }}</div>
                         <div class="text-slate-200 mt-1">Rarity: {{ $rarity }}</div>
                     </div>
                 </div>
             </div>

             <div>
                 <a href="{{ url('/yugioh/card/' . ($card['id'] ?? '')) }}" class="text-white font-semibold block truncate">{{ $name }}</a>
                 <p class="text-slate-400 text-sm mt-1">{{ $type }}</p>
                 <p class="text-slate-400 text-sm mt-1">{{ $rarity }}</p>
             </div>
         </div>

         @endforeach
     </div>
     @else
     <div class="py-6 text-center text-slate-400">No cards found in this pack.</div>
     @endif
 </div>