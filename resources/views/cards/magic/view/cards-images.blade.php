<div class="relative grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 p-2">
    @foreach($cards as $card)

    <x-card :desc="$card['oracle_text'] ?? 'No description available'">
        <!-- Card Image -->
        <a href="{{ url('/magic/card/' . $card['id']) }}">
            <img src="{{ $card['image_uris']['normal']
                    ?? $card['card_faces'][0]['image_uris']['normal']
                    ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                alt="{{ $card['name'] }}"
                class="max-w-full max-h-64 w-auto h-auto object-contain rounded">
        </a>
        <!-- Hover Overlay -->
        <x-card-hover-overlay
            :name="$card['name']"
            :underTitle="$card['type_line'] ?? 'unknown'"
            :url="url('/magic/card/' . $card['id'])">

            @if(isset($card['power']) && isset($card['toughness']))
            <p class="text-[10px] mt-1">Power: {{ $card['power'] }} | Toughness: {{ $card['toughness'] }}</p>
            @endif
        </x-card-hover-overlay>
    </x-card>

    @endforeach
</div>