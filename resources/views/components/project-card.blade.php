@props(['title', 'subtitle' => '', 'modalId'])

<div
    class="cursor-pointer bg-white p-6 rounded-xl shadow hover:shadow-xl transition"
    data-modal-target="{{ $modalId }}">
    <h2 class="text-xl font-semibold">{{ $title }}</h2>
    <p class="text-gray-500 text-sm">{{ $subtitle }}</p>

    <div class="mt-3 text-gray-600 text-sm">
        {{ $slot }}
    </div>
</div>