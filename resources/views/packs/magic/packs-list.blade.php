<div class="w-full flex justify-center">
    <div class="overflow-hidden rounded-xl shadow-lg border border-gray-200 w-full ">
        <table class="min-w-full border-collapse divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Icon</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Code</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Released</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Card Count</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Link</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($packs as $set)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        @if(!empty($set['icon_svg_uri']))
                        <img src="{{ $set['icon_svg_uri'] }}" class="w-6 h-6" />
                        @endif
                    </td>

                    <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $set['name'] }}
                    </td>

                    <td class="px-4 py-3 text-gray-700 font-mono">
                        {{ strtoupper($set['code']) }}
                    </td>

                    <td class="px-4 py-3 text-gray-700 capitalize">
                        {{ str_replace('_', ' ', $set['set_type']) }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                        {{ $set['released_at'] ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                        {{ $set['card_count'] }}
                    </td>

                    <td class="px-4 py-3">
                        <a href="{{ url('/magic/pack/' . urlencode($set['code'])) }}"
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