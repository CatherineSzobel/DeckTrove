{{-- resources/views/components/cards-table.blade.php --}}
@props(['cards' => []])

<div class="overflow-x-auto">
    <table class="min-w-full border-collapse divide-y divide-gray-300">
        <thead>
            <tr>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Name</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">cmc</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Oracle Text</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Mana Cost</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Type</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Rarity</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Set</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cards as $card)
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="px-3 py-2 text-sm text-gray-800 underline hover:decoration-blue-500 hover:text-blue-600">
                    <a href="{{ url('/magic/card/' . $card['id']) }}">
                        {{ $card['name'] ?? 'Unknown' }}
                    </a>
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $card['cmc'] ?? 'Unknown' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    @if(!empty($card['oracle_text']))
                    {!! nl2br(e($card['oracle_text'])) !!}
                    @endif
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $card['mana_cost'] ?? '-' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $card['type_line'] ?? '-' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800 text-{{ 
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
                    {{ $card['rarity'] ?? '-' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $card['set_name'] ?? 'Unknown' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ ($card['prices']['usd'] ?? 'Unknown') . '$' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>