{{-- resources/views/components/cards-table.blade.php --}}
@props(['cards' => []])

<div class="overflow-x-auto">
    <table class="min-w-full border-collapse divide-y divide-gray-300">
        <thead>
            <tr>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Name</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Type</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Race</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Attribute</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">ATK</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">DEF</th>
                <th class="text-left px-3 py-2 font-medium text-gray-700">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cards as $card)
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="px-3 py-2 text-sm text-gray-800 underline hover:decoration-blue-500 hover:text-blue-600">
                    <a href="{{ url('/yugioh/card/' . $card['id']) }}">
                        {{ $card['name'] ?? 'Unknown' }}
                    </a>
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $card['type'] ?? 'Unknown' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $card['race'] ?? 'Unknown' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $card['attribute'] ?? '-' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $card['atk'] ?? '-' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $card['def'] ?? '-' }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ ($card['card_prices'][0]['cardmarket_price'] ?? 'Unknown') . '$' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>