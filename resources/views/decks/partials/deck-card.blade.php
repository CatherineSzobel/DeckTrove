@forelse($decks as $deck)
<a href="{{ route('decks.show', $deck->id) }}"
    class="group relative flex flex-col bg-gradient-to-b from-gray-50 via-gray-100 to-gray-200 rounded-2xl border border-gray-200 shadow-md hover:shadow-lg transform transition-all duration-300 hover:-translate-y-1 overflow-hidden">

    <div class="h-44 w-full relative overflow-hidden rounded-t-2xl">
        @if($deck->image)
        <img src="{{ $deck->image }}" alt="{{ $deck->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
        <div class="w-full h-full flex items-center justify-center bg-gray-300">
            <span class="text-6xl text-gray-400">🃏</span>
        </div>
        @endif
        <div class="absolute top-3 right-3 bg-gray-900/70 text-white text-xs font-medium px-3 py-1 rounded-full shadow">
            {{ $deck->cards->sum('pivot.count') ?? 0 }} cards
        </div>
    </div>

    <div class="p-5 flex flex-col justify-between flex-1">
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-gray-700 transition">
                    {{ $deck->name }}
                </h3>
                <span class="text-xs px-2 py-1 rounded-full bg-gray-300 text-gray-700 font-medium">
                    {{ ucfirst($deck->game) }}
                </span>
            </div>
            <p class="text-sm text-gray-600 line-clamp-3">
                {{ $deck->description ?: 'No description provided.' }}
            </p>
        </div>

        <div class="flex items-center justify-between mt-4 text-xs text-gray-500">
            <span>By {{ $deck->user->username ?? 'Unknown' }}</span>
            <span class="text-gray-700 group-hover:text-gray-900 font-medium transition">View →</span>
        </div>
    </div>
</a>
@empty
<div class="col-span-full text-center text-gray-500 py-12">
    No public decks found.
</div>
@endforelse