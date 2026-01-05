@props(['name' => '', 'contents' => [], 'urlPrefix' => '/yugioh/card/', 'cardId' => null])

<tr class="hover:bg-gray-50 transition-colors">
    <td class="px-4 py-2 text-sm font-semibold text-gray-800 underline hover:decoration-blue-500 hover:text-blue-600">
        <a href="{{ url($urlPrefix . $cardId) }}">
            {{ $name ?? 'Unknown' }}
        </a>
    </td>

    @foreach($contents as $index => $content)
        @php
            $textClass = '';
            if (strtolower($content ?? '') === 'common') $textClass = 'text-gray-600';
            elseif (strtolower($content ?? '') === 'uncommon') $textClass = 'text-green-700';
            elseif (strtolower($content ?? '') === 'rare') $textClass = 'text-yellow-600';
            elseif (strtolower($content ?? '') === 'mythic') $textClass = 'text-orange-600';
            elseif (strtolower($content ?? '') === 'special') $textClass = 'text-purple-600';
            elseif (strtolower($content ?? '') === 'bonus') $textClass = 'text-blue-600';
        @endphp

        <td class="px-4 py-2 text-sm text-gray-800 {{ $index === 2 ? 'whitespace-pre-line max-w-xs' : '' }} {{ $textClass }}">
            {!! $index === 2 ? $content : e($content) !!}
        </td>
    @endforeach
</tr>
