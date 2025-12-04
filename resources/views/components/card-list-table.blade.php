@props(['headers' => []])
<table class="min-w-full border-collapse divide-y divide-gray-300">
    <thead>
        <tr>
            @foreach($headers as $header)
                <th class="text-left px-3 py-2 font-medium text-gray-700">{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        {{ $slot }}
    </tbody>
</table>