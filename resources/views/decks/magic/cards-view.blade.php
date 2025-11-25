<div class="p-3 bg-white rounded shadow search-pool max-h-[600px]  overflow-visible">
    <div class="max-h-[600px] overflow-y-auto overflow-x-hidden">
        @foreach ($cards as $card)
        <div class="relative w-32 h-44 mb-4 mx-auto group card-wrapper cursor-pointer"
            data-card-id="{{ $card['id'] }}">

            <!-- Minus Button (Top Left) -->
            <button class="minus-btn absolute top-1 left-1 z-20 px-1 py-0.5 bg-red-500 text-white rounded text-xs">
                -
            </button>

            <!-- Plus Button (Top Right) -->
            <button class="plus-btn absolute top-1 right-1 z-20 px-1 py-0.5 bg-green-500 text-white rounded text-xs">
                +
            </button>

            <!-- Card Image -->
            <img
                src="{{ $card['image_uris']['small'] ?? ($card['card_faces'][0]['image_uris']['small'] ?? '') }}"
                alt="{{ $card['name'] }}"
                class="w-full h-full object-cover rounded card">
            <!-- Hover Overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 group-hover:opacity-100 transition-opacity rounded p-2 flex flex-col justify-center items-center text-center">
                <a href="{{ url('/magic/card/' . $card['id']) }}" target="_blank">
                    <h3 class=" font-bold text-sm">{{ $card['name'] }}</h3>
                </a>
                <p class="text-xs mt-1">{{ $card['type_line'] ?? $card['desc'] ?? '' }}</p>
            </div>
            <!-- Description (appears to the right on hover) -->
            <div class="absolute top-0 left-full ml-2 w-48 bg-black bg-opacity-80 text-white text-xs p-2 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50">
                <p>{{ $card['oracle_text'] ?? 'No description available' }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>