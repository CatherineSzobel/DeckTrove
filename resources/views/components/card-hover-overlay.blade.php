<div class="absolute inset-0
            bg-gradient-to-t from-black/70 via-black/40 to-transparent
            text-white
            opacity-0 group-hover:opacity-100
            transition-all duration-200
            p-3
            flex flex-col justify-end text-left
            z-30
            pointer-events-none">

    <h3 class="font-semibold text-sm leading-tight">
        {{ $name }}
    </h3>

    <p class="text-xs text-gray-200">
        {{ $underTitle }}
    </p>

    {{ $slot }}
</div>
