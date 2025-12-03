@props(['label','color' => 'bg-gray-300 text-black'])

<span class="px-3 py-1 text-xs font-medium {{ $color }} rounded-full border border-gray-200">
    {{ $label }}
</span>