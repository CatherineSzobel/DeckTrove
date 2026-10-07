<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @foreach ($cards as $card)
    <div class="rounded-xl bg-white shadow hover:shadow-lg transition-transform transform hover:scale-105 flex flex-col h-full overflow-hidden">
        <a href="{{ $card->link() }}" target="_blank" class="relative group">
            <img src="{{ $card->image() }}" alt="{{ $card->name() }}" loading="lazy"
                class="w-full h-64 object-cover rounded-t-lg transition-transform duration-300 group-hover:scale-105 object-top">
        </a>

        <div class="p-4 flex flex-col flex-1">
            <h3 class="text-lg font-semibold mb-2 text-gray-800">{{ $card->name() }}</h3>

            <div class="flex justify-between items-center gap-2 mb-2 text-sm text-gray-600">
                <span>{{ $card->type() }}</span>
                <span class="text-gray-500 text-right">{{ $card->subtype() }}</span>
            </div>

            @if ($card->hasStats())
            <div class="text-sm font-medium text-gray-700 mb-2">{{ $card->statLine() }}</div>
            @endif

            @if ($card->description())
            <div class="text-sm text-gray-700 mb-3 leading-relaxed whitespace-pre-line">{{ $card->description() }}</div>
            @endif

            <div class="flex justify-between items-center text-xs text-gray-500 mt-auto mb-2">
                <span>{{ $card->setName() }}</span>
                <span class="{{ $card->rarityColor() }}">{{ $card->rarityLabel() }}</span>
            </div>

            <div class="mt-2 font-semibold text-green-600 text-right">${{ $card->price() }}</div>
        </div>
    </div>
    @endforeach
</div>
