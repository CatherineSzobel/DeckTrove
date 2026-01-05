<x-layout :js="['resources/js/deck.js']">
  <div class="max-w-6xl mx-auto px-4 py-10">
    <div class="text-center mb-8">
      <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900">{{ $deck->name }}</h1>
      <p class="text-sm md:text-base text-gray-400">{{ $deck->description }}</p>
      <p class="text-sm text-gray-500 mt-2">
        Total cards: <span class="font-semibold text-amber-400">{{ $deck->cards->sum('pivot.count') }}</span>
      </p>
    </div>

    @php
    $zoneTitles = [
    'main' => 'Main Deck',
    'extra' => 'Extra Deck',
    'side' => 'Side Deck',
    ];
    @endphp

    @foreach ($zoneTitles as $zone => $title)
    @if(isset($sections[$zone]) && $sections[$zone]->isNotEmpty())
    <div class="mb-10">
      <div class="flex items-center justify-between cursor-pointer bg-gray-100 p-3 rounded-lg shadow-sm hover:bg-gray-200" data-zone="{{ $zone }}">
        <h2 class="text-2xl font-bold text-gray-800">{{ $title }}</h2>
        <div class="flex items-center space-x-2">
          <span class="text-gray-500 text-sm">{{ $sections[$zone]->count() }} cards</span>
          <button id="{{ $zone }}-toggle-btn" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300 text-sm text-gray-700">−</button>
        </div>
      </div>

      <div id="{{ $zone }}-cards" class=" card mt-4 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
        @foreach ($sections[$zone] as $card)
        @php
        $img = data_get($card, 'card_images.0.image_url_small')
        ?: data_get($card, 'image_uris.normal')
        ?: data_get($card, 'image_uris.small')
        ?: data_get($card, 'image');
        @endphp

        <div class="relative group inline-block">
          <a href="{{ url('/'. $deck->game .'/card/' . $card['id']) }}" target="_blank" rel="noopener noreferrer"> 
          <img src="{{ $img }}" alt="Card" class="w-20 h-auto rounded-sm shadow-sm cursor-pointer transition-transform duration-200 hover:scale-105">
          </a>
          <div class="absolute z-50 hidden w-150 p-4 bg-gray-900 text-white rounded-lg shadow-lg border border-gray-700 -top-2 left-full ml-2">
            <div class="flex space-x-3">
              <img src="{{ $img }}" alt="Card" class="w-24 h-auto rounded-sm border border-gray-600">
              <div class="flex-1">
                <h3 class="font-bold text-lg">{{ $card['name'] ?? 'Card Name' }}</h3>
                <p class="text-sm text-gray-300 mt-1">{{ $card['type'] ?? $card['card_type'] ?? 'Type' }}</p>
                @if(isset($card['atk']) && isset($card['def']))
                <p class="text-sm mt-1">ATK/{{ $card['atk'] }} DEF/{{ $card['def'] }}</p>
                @endif
                <p class="text-xs mt-2 text-gray-200">{{ $card['desc'] ?? $card['oracle_text'] ?? 'No description' }}</p>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    @endif
    @endforeach

    @if(collect($sections)->every(fn($s) => $s->isEmpty()))
    <div class="mt-12 text-center text-gray-500">No cards in this deck yet.</div>
    @endif
  </div>
</x-layout>