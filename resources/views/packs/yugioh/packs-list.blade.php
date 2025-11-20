<div class="w-full flex justify-center">
    <table class="border-collapse divide-y divide-gray-300 mx-auto w-auto max-w-4xl">
        <thead>
            <tr>
                <th class="px-3 py-2 text-left font-medium text-gray-700">Image</th>
                <th class="px-3 py-2 text-left font-medium text-gray-700">Set Name</th>
                <th class="px-3 py-2 text-left font-medium text-gray-700">Release Date</th>
                <th class="px-3 py-2 text-left font-medium text-gray-700">Set Code</th>
                <th class="px-3 py-2 text-left font-medium text-gray-700">Cards</th>
            </tr>
        </thead>

        <tbody>
            @foreach($packs as $pack)
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="px-3 py-2 text-sm text-gray-800">
                    <img src="{{ $pack['set_image'] ?? 'https://via.placeholder.com/80x110?text=No+Image' }}"
                        alt="{{ $pack['set_name'] }}"
                        class="w-16 h-auto rounded shadow">
                </td>

                <td class="px-3 py-2 text-sm text-gray-800 font-medium">
                    <a href="{{ url('/yugioh/pack/' . urlencode($pack['set_code'])) }}"
                        class="underline hover:text-blue-600">
                        {{ $pack['set_name'] }}
                    </a>
                </td>

                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $pack['tcg_date'] ?? 'Unknown' }}
                </td>

                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $pack['set_code'] }}
                </td>

                <td class="px-3 py-2 text-sm text-gray-800">
                    {{ $pack['num_of_cards'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>