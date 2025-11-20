<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @foreach($cards as $card)

    <div class="relative group bg-white rounded-lg shadow hover:shadow-lg p-2 flex justify-center transition transform hover:-translate-y-1 card-with-tooltip"
        data-desc="{{ $card['oracle_text'] ?? 'No description available' }}">

        <!-- Card Image -->
        <a href="{{ url('/magic/card/' . $card['id']) }}">
            <img src="{{ $card['image_uris']['normal'] 
                ?? $card['card_faces'][0]['image_uris']['normal'] 
                ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                alt="{{ $card['name'] }}"
                class="max-w-full max-h-64 w-auto h-auto object-contain rounded">
        </a>

        <!-- Hover Overlay (Same style as Yugioh) -->
        <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 group-hover:opacity-100 transition-opacity rounded p-2 flex flex-col justify-center items-center text-center z-30">
            <h3 class="font-bold text-xs">{{ $card['name'] }}</h3>
        </div>

    </div>
    @endforeach
</div>
<div id="global-tooltip" class="fixed top-0 left-0 z-50 pointer-events-none opacity-0 transition-opacity">

</div>