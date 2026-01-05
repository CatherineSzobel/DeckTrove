<div class="p-3 bg-white rounded shadow search-pool 
            max-h-[600px] w-full md:w-[400px] lg:w-[600px] 
            overflow-x-hidden">

    @foreach ($cards as $card)
    @php
    $cardId = data_get($card, 'id');
    $cardName = data_get($card, 'name', 'Unknown');
  
    $cardSubtype = data_get($card, $seriesConfig['subtype_field'] ?? 'race', 'Unknown');

    $desc = isset($card['card_faces']) && is_array($card['card_faces']) && count($card['card_faces']) > 0
    ? ($seriesConfig['colorless_description'] ?? fn($c) => '')($card)
    : ($seriesConfig['description'] ?? fn($c) => '')($card);

    $type = isset($card['card_faces']) && is_array($card['card_faces']) && count($card['card_faces']) > 0
    ? ($seriesConfig['colorless_type'] ?? fn($c) => '')($card)
    : ($seriesConfig['type_field'] ?? fn($c) => '')($card);

    $cardImage = ($seriesConfig['image'] ?? fn($c) => '')($card);
    @endphp

    <div class="relative w-32 h-44 mb-4 mx-auto group card-wrapper cursor-pointer"
        data-card-id="{{ $cardId }}"
        data-card-name="{{ $cardName }}"
        data-card-image="{{ $cardImage }}"
        data-card-type="{{ $type }}"
        data-card-race="{{ $cardSubtype }}"
        data-card-desc="{{ $desc }}">

        <button class="minus-btn absolute top-1 left-1 z-20 px-1 py-0.5 bg-red-500 text-white rounded text-xs">
            -
        </button>

        <button class="plus-btn absolute top-1 right-1 z-20 px-1 py-0.5 bg-green-500 text-white rounded text-xs">
            +
        </button>

        <a
            class="drag-link block w-full h-full"
            draggable="true"
            data-card-id="{{ $cardId }}"
            data-card-name="{{ $cardName }}"
            data-card-image="{{ $cardImage }}"
            data-card-type="{{ $type }}"
            data-card-race="{{ $cardSubtype }}"
            data-card-desc="{{ $desc }}">

            <img
                src="{{ $cardImage }}"
                alt="{{ $cardName }}"
                class="w-full h-full object-cover rounded card" />

            <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0
                            group-hover:opacity-100 transition-opacity rounded p-2
                            flex flex-col justify-center items-center text-center
                            pointer-events-none">

                <h3 class="font-bold text-xs">{{ $cardName }}</h3>

                <p class="text-[10px] mt-1">
                    {{ $type }} / {{ $cardSubtype }}
                </p>
            </div>
        </a>

        <div id="descriptionBox" class="absolute top-0 left-full ml-2 w-48 bg-black bg-opacity-80
                        text-white text-xs p-2 rounded opacity-0 group-hover:opacity-100
                        transition-opacity z-10 pointer-events-none">
            <p>{{ $desc }}</p>
        </div>

    </div>
    @endforeach
</div>