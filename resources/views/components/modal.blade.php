@props(['id'])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-center justify-center">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50" data-close-modal></div>

    <!-- Modal content -->
    <div class="relative bg-white rounded-2xl shadow-xl max-w-3xl w-full p-6 z-10">
        <button data-close-modal class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            &times;
        </button>

        {{ $slot }}
    </div>
</div>