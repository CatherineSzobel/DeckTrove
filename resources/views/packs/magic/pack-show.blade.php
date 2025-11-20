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
<div class="overflow-x-auto mt-6">
    <table class="w-full border-collapse border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border">Image</th>
                <th class="p-2 border">Name</th>
                <th class="p-2 border">Type</th>
                <th class="p-2 border">Rarity</th>
                <th class="p-2 border">Mana Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cards as $card)
            <tr class="hover:bg-gray-50">

                {{-- IMAGE WITH HOVER INFO --}}
                <td class="p-2 border relative group">
                    <img src="{{ $card['image_uris']['small'] ?? ($card['card_faces'][0]['image_uris']['small'] ?? '') }}"
                        alt="{{ $card['name'] ?? 'Unknown' }}"
                        class="w-20 h-auto rounded shadow transition-transform duration-300 group-hover:scale-150">

                    {{-- HOVER INFO --}}
                    <div class="absolute top-0 left-0 w-64 bg-white border border-gray-300 rounded shadow-lg p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-50 pointer-events-none">
                        <p class="font-bold">{{ $card['name'] ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-700 mb-1">Type: {{ $card['type_line'] ?? ($card['card_faces'][0]['type_line'] ?? '-') }}</p>
                        <p class="text-sm text-gray-700 mb-1">Rarity: {{ $card['rarity'] ?? '-' }}</p>
                        <p class="text-sm text-gray-700 mb-1">Mana Cost: {{ $card['mana_cost'] ?? ($card['card_faces'][0]['mana_cost'] ?? '-') }}</p>
                        @if(!empty($card['oracle_text']))
                        <p class="text-sm text-gray-800 mt-2">{{ $card['oracle_text'] }}</p>
                        @elseif(!empty($card['card_faces'][0]['oracle_text']))
                        <p class="text-sm text-gray-800 mt-2">{{ $card['card_faces'][0]['oracle_text'] }}</p>
                        @endif
                    </div>
                </td>

                {{-- NAME --}}
                <td class="p-2 border">
                    <a href="{{ url('/magic/card/' . $card['id']) }}" class="text-blue-600 underline">
                        {{ $card['name'] ?? 'Unknown' }}
                    </a>
                </td>

                {{-- TYPE --}}
                <td class="p-2 border">
                    {{ $card['type_line'] ?? ($card['card_faces'][0]['type_line'] ?? 'Unknown') }}
                </td>

                {{-- RARITY --}}
                <td class="p-2 border capitalize">
                    {{ $card['rarity'] ?? 'Unknown' }}
                </td>

                {{-- MANA COST --}}
                <td class="p-2 border">
                    {{ $card['mana_cost'] ?? ($card['card_faces'][0]['mana_cost'] ?? '-') }}
                </td>

            </tr>
            @empty
            <tr>
                <td class="p-2 border text-center" colspan="5">No cards found in this set.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>