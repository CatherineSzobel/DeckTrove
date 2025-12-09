<x-layout>
  <div class="max-w-6xl mx-auto px-4 py-10">
    <!-- Deck header -->
    <div class="text-center mb-8">
      <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900">{{ $deck->name }}</h1>
      <p class="text-sm md:text-base text-gray-400">{{ $deck->description }}</p>
      <p class="text-sm text-gray-500 mt-2">
        Total cards: <span class="font-semibold text-amber-400">{{ $deck->cards->sum('pivot.count') }}</span>
      </p>
    </div>

    <!-- Cards grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
      @foreach ($cards as $card)
        @php
          $img = data_get($card, 'card_images.0.image_url_small')
                 ?: data_get($card, 'image_uris.normal')
                 ?: data_get($card, 'image');
          $name = $card['name'] ?? $card['card_name'] ?? 'Card';
          // If using pivot count to expand duplicates, you might loop multiple times or show count badge
          $count = $card['pivot']['count'] ?? 1;
        @endphp

        <div class="group relative bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-200">
          <img src="{{ $img }}" alt="{{ $name }}"
               class="w-full h-52 sm:h-64 md:h-72 object-cover">

          <div class="p-2 md:p-3">
            <h3 class="text-sm md:text-base font-semibold text-gray-800 truncate">{{ $name }}</h3>
            @if ($count > 1)
              <span class="inline-block text-xs text-gray-500 mt-1">x{{ $count }}</span>
            @endif
          </div>

          <!-- Optional: overlay on hover, like ygoprodeck shows some card details -->
          <div class="absolute inset-0 bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
            <span class="text-white text-xs uppercase bg-black/50 px-2 py-1 rounded">View</span>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Optional: fallback if no cards -->
    @if($cards->isEmpty())
      <div class="mt-12 text-center text-gray-500">No cards in this deck yet.</div>
    @endif
  </div>
</x-layout>
