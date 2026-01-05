<div class="w-full flex justify-center py-6">
    <div class="overflow-hidden rounded-xl shadow-lg border border-gray-200 w-full">
        <table class="min-w-full border-collapse divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Image</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    @if(isset($seriesConfig['pack']['type']))
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                    @endif
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Released</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Card Count</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Link</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($packs as $pack)
                @php
                $packConfig = $seriesConfig['pack'] ?? [];

                $code = data_get($pack, $packConfig['code'] ?? 'code');
                $name = data_get($pack, $packConfig['name'] ?? 'name');
                $release = data_get($pack, $packConfig['release_date'] ?? '-');
                $cardCount = data_get($pack, $packConfig['card_count'] ?? '-');
                $type = data_get($pack, $packConfig['type'] ?? null);
                $image = data_get($pack, $packConfig['image'] ?? null);
                $linkPrefix = $seriesConfig['link_prefix'] . '/pack/' ?? '/';
                @endphp

                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        @if($image)
                        <img src="{{ $image }}" alt="{{ $name }}" class="w-16 h-auto rounded-md shadow-sm">
                        @else
                        <div class="w-16 h-16 bg-gray-300 rounded flex items-center justify-center text-gray-500">
                            {{ strtoupper(substr($series,0,2)) }}
                        </div>
                        @endif
                    </td>

                    <td class="px-4 py-3 font-medium text-gray-800">{{ $name }}</td>
                    @if(isset($packConfig['type']))
                    <td class="px-4 py-3 text-gray-700 capitalize">{{ str_replace('_',' ',$type) }}</td>
                    @endif
                    <td class="px-4 py-3 text-gray-700">{{ $release }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $cardCount }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ url($linkPrefix . 'pack/' . urlencode($code)) }}"
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