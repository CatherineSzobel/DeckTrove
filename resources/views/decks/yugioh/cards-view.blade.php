<div class="p-3 bg-white rounded shadow search-pool 
            max-h-[600px] w-full md:w-[400px] lg:w-[600px] 
            overflow-x-hidden">
    @foreach ($cards as $card)
    <div class="relative w-32 h-44 mb-4 mx-auto group card-wrapper cursor-pointer"
        data-card-id="{{ $card['id'] }}"
        data-card-name="{{ $card['name'] }}"
        data-card-image="{{ $card['card_images'][0]['image_url_small'] ?? $card['card_images'][0]['image_url'] }}"
        data-card-type="{{ $card['type'] ?? '' }}"
        data-card-race="{{ $card['race'] ?? '' }}"
        data-card-desc="{{ $card['desc'] ?? '' }}">


        <!-- Minus Button (Top Left) -->
        <button class="minus-btn absolute top-1 left-1 z-20 px-1 py-0.5 bg-red-500 text-white rounded text-xs">
            -
        </button>

        <!-- Plus Button (Top Right) -->
        <button class="plus-btn absolute top-1 right-1 z-20 px-1 py-0.5 bg-green-500 text-white rounded text-xs">
            +
        </button>

        <!-- Card Image -->
        <img src="{{ $card['card_images'][0]['image_url_small'] ?? $card['card_images'][0]['image_url'] }}"
            alt="{{ $card['name'] }}"
            class="w-full h-full object-cover rounded card">

        <!-- Hover Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0
                    group-hover:opacity-100 transition-opacity rounded p-2
                    flex flex-col justify-center items-center text-center pointer-events-auto">

            <a href="{{ url('/yugioh/card/' . $card['id']) }}"
                class="drag-link pointer-events-auto text-white" draggable="true"
                data-card-id="{{ $card['id'] }}">
                <h3 class="font-bold text-xs">{{ $card['name'] }}</h3>
            </a>

            <p class="text-[10px] mt-1 pointer-events-none">
                {{ $card['type'] ?? 'Unknown' }} / {{ $card['race'] ?? 'Unknown' }}
            </p>
        </div>

        <!-- Description box -->
        <div class="absolute top-0 left-full ml-2 w-48 bg-black bg-opacity-80
                    text-white text-xs p-2 rounded opacity-0 group-hover:opacity-100
                    transition-opacity z-10 pointer-events-none">
            <p>{{ $card['desc'] ?? 'No description available' }}</p>
        </div>

    </div>
    @endforeach
</div>