<div class="relative grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 p-2">
    @foreach($cards as $card)
    <div class="relative group">
        <div class="bg-white rounded-lg shadow hover:shadow-lg p-2 flex justify-center 
                    transition transform hover:-translate-y-1 overflow-visible">

            <a href="{{ url('/yugioh/card/' . $card['id']) }}">
                <img src="{{ $card['card_images'][0]['image_url'] ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                    alt="{{ $card['name'] }}"
                    class="w-full h-auto object-contain rounded"
                    loading="lazy">
            </a>

            <!-- Hover Overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 
                        group-hover:opacity-100 transition-opacity rounded p-2 flex flex-col 
                        justify-center items-center text-center z-30 pointer-events-none">

                <a href="{{ url('/yugioh/card/' . $card['id']) }}" target="_blank">
                    <h3 class="font-bold text-xs">{{ $card['name'] }}</h3>
                </a>

                <p class="text-[10px] mt-1">
                    {{ $card['type'] ?? 'Unknown' }} / {{ $card['race'] ?? 'Unknown' }}
                </p>

                @if(isset($card['atk']) && isset($card['def']))
                <p class="text-[10px] mt-1">ATK: {{ $card['atk'] }} | DEF: {{ $card['def'] }}</p>
                @elseif(isset($card['atk']))
                <p class="text-[10px] mt-1">ATK: {{ $card['atk'] }} / LINK: {{ $card['linkval'] ?? '' }}</p>
                @endif
            </div>
        </div>
        <!-- Description Tooltip -->
        <div class="absolute top-0 left-full ml-2 w-48 bg-black bg-opacity-80 text-white 
                    text-xs p-2 rounded opacity-0 group-hover:opacity-100 transition-opacity 
                    z-40 pointer-events-none">
            <p>{{ $card['desc'] ?? 'No description available' }}</p>
        </div>

    </div>
    @endforeach
</div>