@props([
    'class' => 'flex flex-col', 
    'routes' => [],
    'titles' => [],
    'colors' => [],
])

<div {{ $attributes->merge(['class' => $class]) }}>
    @foreach ($routes as $index => $route)
        @php
            $url = $route === '#' ? '#' : route($route);
            $colorClasses = $colors[$index] ?? 'bg-slate-800 hover:bg-slate-700';
            $title = $titles[$index] ?? 'Link';
        @endphp

        <a href="{{ $url }}"
           class="{{ $colorClasses }} group flex items-center justify-center gap-2 px-6 py-4 rounded-xl text-white font-semibold shadow-md transition-all transform hover:scale-105 hover:shadow-lg mb-4">
            {{ $title }}
        </a>
    @endforeach
</div>
