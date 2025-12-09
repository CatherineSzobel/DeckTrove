<div class="w-full flex justify-center py-6">
    <div class="overflow-hidden rounded-xl shadow-lg border border-gray-200 w-full">
        <table class="min-w-full border-collapse divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Image</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Set Name</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Release Date</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Set Code</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Cards</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Link</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($packs as $pack)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <img src="{{ $pack['set_image'] ?? 'https://via.placeholder.com/80x110?text=No+Image' }}"
                            alt="{{ $pack['set_name'] }}"
                            class="w-16 h-auto rounded-md shadow-sm">
                    </td>

                    <td class="px-4 py-3 text-gray-800 font-medium">
                        {{ $pack['set_name'] }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                        {{ $pack['tcg_date'] ?? 'Unknown' }}
                    </td>

                    <td class="px-4 py-3 text-gray-700 font-mono">
                        {{ $pack['set_code'] }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                        {{ $pack['num_of_cards'] }}
                    </td>

                    <td class="px-4 py-3">
                        <a href="{{ url('/yugioh/pack/' . urlencode($pack['set_code'])) }}"
                            class="text-blue-600 hover:text-blue-700 underline font-medium">
                            View Cards
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>