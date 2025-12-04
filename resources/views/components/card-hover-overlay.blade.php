@props(['name' => 'Card Name', 'underTitle' => 'unknown', 'url' => '#'])

<div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0
            group-hover:opacity-100 transition-opacity rounded p-2 flex flex-col
            justify-center items-center text-center z-30 pointer-events-none">

    <a href="{{ $url }}" target="_blank">
        <h3 class="font-bold text-xs">{{ $name }}</h3>
    </a>

    <p class="text-[10px] mt-1">
        {{ $underTitle }}
    </p>

    {{ $slot }}
</div>