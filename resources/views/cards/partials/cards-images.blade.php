<div class="relative grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 p-2">
    @foreach ($cards as $card)
    <x-card :desc="$card->description()">
        <a href="{{ $card->link() }}" target="_blank">
            <img src="{{ $card->image() }}" alt="{{ $card->name() }}" loading="lazy"
                class="max-w-full max-h-64 w-auto h-auto object-contain rounded">
        </a>

        <x-card-hover-overlay :name="$card->name()" :underTitle="$card->subtype()">
            @if ($card->hasStats())
            <p class="text-[10px] mt-1">{{ $card->statLine() }}</p>
            @endif
        </x-card-hover-overlay>
    </x-card>
    @endforeach
</div>
