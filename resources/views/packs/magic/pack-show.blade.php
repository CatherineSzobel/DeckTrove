{{-- Set Info --}}
<div class="py-6 flex flex-col md:flex-row items-start md:items-center gap-6">

    {{-- SET ICON / SYMBOL --}}
    <img src="{{ $set['icon_svg_uri'] ?? '' }}"
        alt="{{ $set['name'] ?? '' }}"
        class="w-20 h-auto">

    <div>
        <h1 class="text-3xl font-bold mb-2">{{ $set['name'] ?? 'Unknown Set' }}</h1>
        <p class="mb-1">Code: {{ strtoupper($set['code'] ?? '-') }}</p>
        <p class="mb-1">Set Type: {{ ucfirst(str_replace('_', ' ', $set['set_type'] ?? '-')) }}</p>
        <p class="mb-1">Card Count: {{ $set['card_count'] ?? '-' }}</p>
        <p class="mb-1">Released: {{ $set['released_at'] ?? 'Unknown' }}</p>
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
            ->pluck('rarity')
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
         <a href="/magic/packs" class="inline-block bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded">Back to Database</a>
     </div>
</div>

{{-- Cards Grid --}}
<div class="mt-6 max-h-[600px] overflow-y-auto">
    @if(!empty($cards) && count($cards))
    <div id="cards-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($cards as $card)
        @php
        $img = $card['image_uris']['small'] ?? ($card['card_faces'][0]['image_uris']['small'] ?? '');
        $name = $card['name'] ?? 'Unknown';
        $type = $card['type_line'] ?? ($card['card_faces'][0]['type_line'] ?? '-');
        $rarity = $card['rarity'] ?? '-';
        $mana = $card['mana_cost'] ?? ($card['card_faces'][0]['mana_cost'] ?? '-');
        @endphp

        <div class="card-item bg-slate-800 rounded-lg border border-slate-700 p-3 hover:shadow-lg transition"
            data-name="{{ strtolower($name) }}"
            data-rarity="{{ strtolower($rarity) }}">

            <div class="h-44 w-full overflow-hidden rounded mb-3">
                @if($img)
                <img src="{{ $img }}" alt="{{ $name }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-slate-400">
                    No Image
                </div>
                @endif
            </div>

            <div>
                <a href="{{ url('/magic/card/' . $card['id']) }}" class="text-white font-semibold block truncate">{{ $name }}</a>
                <p class="text-slate-400 text-sm mt-1">{{ $type }}</p>
                <div class="mt-2 flex items-center justify-between text-sm text-slate-300">
                    <span class="capitalize">{{ $rarity }}</span>
                    <span>{{ $mana }}</span>
                </div>
            </div>
        </div>

        @endforeach
    </div>
    @else
    <div class="py-6 text-center text-slate-400">No cards found in this set.</div>
    @endif
</div>