@foreach($decks as $deck)
<div class="deck-card flex flex-col items-center gap-4 p-6 bg-white border border-gray-200 rounded-xl shadow-lg transform transition hover:scale-105 hover:shadow-2xl" data-game="{{ $deck->game }}">
    <img src="{{ $deck->image ? asset('storage/' . $deck->image) : Vite::asset('resources/img/default-deck.png') }}" alt="{{ $deck->name }}" class="w-48 h-auto rounded-lg">
    <h2 class="font-bold text-xl text-gray-900">{{ $deck->name }}</h2>
    <p class="text-gray-500 text-center">{{ $deck->description }}</p>
    <p class="text-gray-700 font-medium">Author: {{ $deck->user->name ?? 'Unknown' }}</p>
</div>
@endforeach