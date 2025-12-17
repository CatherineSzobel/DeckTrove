@props(['title'])

<div class=" sticky top-0 z-40 mb-6 rounded-2xl bg-white/90 backdrop-blur shadow-sm">
    <div class="px-6 py-5">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
            {{ $title }}
        </h1>
    </div>

    <div class="px-6 pb-5">
        {{ $slot }}
    </div>
</div>
