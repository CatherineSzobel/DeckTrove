<div class="relative grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 p-2">
    @foreach($cards as $card)
    <x-card :desc="$card['desc'] ?? 'No description available'">

        <a href="{{ url('/yugioh/card/' . $card['id']) }}">
            <img src="{{ $card['card_images'][0]['image_url'] ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                alt="{{ $card['name'] }}"
                class="w-full h-auto object-contain rounded"
                loading="lazy">
        </a>

        <!-- Hover Overlay -->
        <x-card-hover-overlay
            :name="$card['name']"
            :underTitle="($card['type'] ?? 'unknown') . ' / ' . ($card['race'] ?? 'unknown')"
            :url="url('/yugioh/card/' . $card['id'])">

            @if(isset($card['atk']) && isset($card['def']))
            <p class="text-[10px] mt-1">ATK: {{ $card['atk'] }} | DEF: {{ $card['def'] }}</p>
            @elseif(isset($card['atk']))
            <p class="text-[10px] mt-1">ATK: {{ $card['atk'] }} / LINK: {{ $card['linkval'] ?? '' }}</p>
            @endif
        </x-card-hover-overlay>
    </x-card>
    @endforeach
</div>