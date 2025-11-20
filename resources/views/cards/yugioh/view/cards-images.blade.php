<div class="relative grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 p-2 overflow-visible">
    @foreach($cards as $card)
    <div class="relative group z-20 bg-white rounded-lg shadow hover:shadow-lg p-2 flex justify-center transition transform hover:-translate-y-1 card-with-tooltip"
        data-desc="{{ $card['desc'] ?? 'No description available' }}">

        <a href="{{ url('/yugioh/card/' . $card['id']) }}">
            <img src="{{ $card['card_images'][0]['image_url'] ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                alt="{{ $card['name'] }}"
                class="w-full h-auto object-contain rounded"
                loading="lazy">
        </a>

        <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 group-hover:opacity-100 transition-opacity rounded p-2 flex flex-col justify-center items-center text-center z-30 pointer-events-none">
            <a href="{{ url('/yugioh/card/' . $card['id']) }}" target="_blank">
                <h3 class="font-bold text-xs">{{ $card['name'] }}</h3>
            </a>
            <p class="text-[10px] mt-1">{{ $card['type'] ?? 'Unknown' }} / {{ $card['race'] ?? 'Unknown' }}</p>
        </div>
    </div>
    @endforeach
</div>

<div id="global-tooltip" class="fixed top-0 left-0 z-50 pointer-events-none opacity-0 transition-opacity"></div>
