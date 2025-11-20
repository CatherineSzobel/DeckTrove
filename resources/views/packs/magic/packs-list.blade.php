<table class="border-collapse divide-y divide-gray-300 mx-auto w-auto max-w-4xl">
    <thead>
        <tr>
            <th class="text-left px-3 py-2 font-medium text-gray-700">Icon</th>
            <th class="text-left px-3 py-2 font-medium text-gray-700">Name</th>
            <th class="text-left px-3 py-2 font-medium text-gray-700">Code</th>
            <th class="text-left px-3 py-2 font-medium text-gray-700">Type</th>
            <th class="text-left px-3 py-2 font-medium text-gray-700">Released</th>
            <th class="text-left px-3 py-2 font-medium text-gray-700">Card Count</th>
            <th class="text-left px-3 py-2 font-medium text-gray-700">Link</th>
        </tr>
    </thead>

    <tbody>
        @foreach($packs as $set)
        <tr class="border-b hover:bg-gray-50 transition">
            <td class="px-3 py-2 text-sm text-gray-800">
                @if(!empty($set['icon_svg_uri']))
                <img src="{{ $set['icon_svg_uri'] }}" class="w-6 h-6" />
                @endif
            </td>

            <td class="px-3 py-2 text-sm text-gray-800 font-medium">
                <a href="{{ url('/magic/pack/' . urlencode($set['code'])) }}"
                    class="underline hover:text-blue-600">
                    {{ $set['name'] }}
                </a>
            </td>

            <td class="px-3 py-2 text-sm text-gray-800">
                {{ strtoupper($set['code']) }}
            </td>

            <td class="px-3 py-2 text-sm text-gray-800 capitalize">
                {{ str_replace('_', ' ', $set['set_type']) }}
            </td>

            <td class="px-3 py-2 text-sm text-gray-800">
                {{ $set['released_at'] ?? '-' }}
            </td>

            <td class="px-3 py-2 text-sm text-gray-800">
                {{ $set['card_count'] }}
            </td>

            <td class="px-3 py-2 text-sm text-blue-600 underline">
                <a href="{{ url('/magic/pack/' . urlencode($set['code'])) }}">
                    View Cards
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>