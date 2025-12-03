@props(['items' => [], 'class' => ''])

@php
$techColors = [
'HTML' => 'bg-orange-500 text-white',
'CSS' => 'bg-blue-500 text-white',
'JavaScript' => 'bg-yellow-400 text-black',
'Tailwind' => 'bg-teal-500 text-white',
'React.js' => 'bg-cyan-600 text-white',
'Laravel' => 'bg-red-600 text-white',
'PHP' => 'bg-blue-600 text-white',
'Node.js' => 'bg-green-600 text-white',
'MySQL' => 'bg-blue-600 text-white',
'Git' => 'bg-orange-600 text-white',
'GitHub' => 'bg-gray-800 text-white',
'Perforce' => 'bg-blue-800 text-white',
'Azure' => 'bg-blue-500 text-white',
'Vite' => 'bg-purple-600 text-white',
'npm' => 'bg-red-500 text-white',
'VsCode' => 'bg-blue-400 text-white',
'Visual Studio' => 'bg-purple-700 text-white',
'Jetbrains' => 'bg-gray-800 text-white',
'Unreal Engine' => 'bg-blue-900 text-white',
'Unity' => 'bg-black text-white',
'C++' => 'bg-blue-700 text-white',
'C#' => 'bg-purple-600 text-white',
'Python (very basic)' => 'bg-yellow-400 text-black',
];
$defaultColor = 'bg-gray-300 text-black';
@endphp

<div class="flex flex-wrap gap-2 mt-3 {{ $class }}">
    <div class="flex flex-wrap gap-2 mt-3 justify-center {{ $class }}">
        @foreach ($items as $item)
            @php
                $color = $techColors[$item] ?? $defaultColor;
            @endphp
        <x-portfolio-tech-badge :label="$item" :color="$color" />

        @endforeach
    </div>
</div>