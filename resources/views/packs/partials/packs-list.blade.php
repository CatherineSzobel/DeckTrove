<div class="w-full flex justify-center py-6">
    <div class="overflow-hidden rounded-xl shadow-lg border border-gray-200 w-full">
        <table class="min-w-full border-collapse divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Image</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    @if($packs->first()?->type())
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                    @endif
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Released</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Card Count</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Link</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($packs as $pack)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        @if($pack->image())
                        <img src="{{ $pack->image() }}"
                            alt="{{ $pack->name() }}"
                            class="w-16 h-auto rounded-md shadow-sm">
                        @else
                        <div class="w-16 h-16 bg-gray-300 rounded flex items-center justify-center text-gray-500">
                            {{ strtoupper(substr($series, 0, 2)) }}
                        </div>
                        @endif
                    </td>

                    <td class="px-4 py-3 font-medium text-gray-800">
                        <a href="{{ $pack->link() }}" class="text-blue-700 hover:text-blue-500 font-medium"> {{ $pack->name() }} </a>
                        @if($pack->code())
                        <span class="text-gray-500 text-sm">({{ $pack->code() }})</span>
                        @endif
                    </td>

                    @if($pack->type())
                    <td class="px-4 py-3 text-gray-700 capitalize">
                        {{ str_replace('_', ' ', $pack->type()) }}
                    </td>
                    @endif

                    <td class="px-4 py-3 text-gray-700">
                        {{ $pack->release() ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                        {{ $pack->cardCount() ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        <a href="{{ $pack->link() }}"
                            class="text-blue-700 hover:text-blue-500 font-medium">
                            View Cards
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>