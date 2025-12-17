@props(['headers' => []])

<div class="overflow-x-auto shadow-md rounded-lg">
    <table class="min-w-full divide-y divide-gray-200 bg-white">
        <thead class="bg-gray-100">
            <tr>
                @foreach($headers as $header)
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            {{ $slot }}
        </tbody>
    </table>
</div>
