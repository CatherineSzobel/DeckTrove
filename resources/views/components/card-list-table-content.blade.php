@props(['name' => '', 'contents' => [], 'urlPrefix' => '', 'cardId' => null])

<tr class="hover:bg-gray-50 transition-colors">
    <td class="px-4 py-2 text-sm font-semibold text-gray-800 underline hover:decoration-blue-500 hover:text-blue-600">
        <a href="{{ url($urlPrefix . $cardId) }}">
            {{ $name ?? 'Unknown' }}
        </a>
    </td>

    @foreach($contents as $index => $content)
        <td class="px-4 py-2 text-sm text-gray-800 {{ $index === 2 ? 'whitespace-pre-line max-w-xs' : '' }}">
            {!! $index === 2 ? $content : e($content) !!}
        </td>
    @endforeach
</tr>
