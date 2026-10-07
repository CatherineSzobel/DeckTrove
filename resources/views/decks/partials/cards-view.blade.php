{{-- Search results in the deck builder. Also returned on its own for infinite scroll. --}}
@foreach ($cards as $card)
<div class="relative w-32 h-44 mb-4 mx-auto group card-wrapper cursor-pointer" draggable="true"
    @foreach ($card->deckBuilderData() as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>

    <button type="button" class="minus-btn absolute top-1 left-1 z-20 px-1 py-0.5 bg-red-500 text-white rounded text-xs disabled:opacity-50"
        aria-label="Remove {{ $card->name() }} from the active zone">-</button>
    <button type="button" class="plus-btn absolute top-1 right-1 z-20 px-1 py-0.5 bg-green-500 text-white rounded text-xs disabled:opacity-50"
        aria-label="Add {{ $card->name() }} to the active zone">+</button>

    <img src="{{ $card->imageSmall() }}" alt="{{ $card->name() }}" loading="lazy" class="w-full h-full object-cover rounded card" />

    <div class="absolute inset-0 bg-black/70 text-white opacity-0 group-hover:opacity-100 transition-opacity rounded p-2 flex flex-col justify-center items-center text-center pointer-events-none">
        <h3 class="font-bold text-xs">{{ $card->name() }}</h3>
        <p class="text-[10px] mt-1">{{ $card->type() }} / {{ $card->subtype() }}</p>
    </div>

    @if ($card->description())
    <div class="absolute top-0 left-full ml-2 w-48 bg-black/80 text-white text-xs p-2 rounded opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none whitespace-pre-line">{{ $card->description() }}</div>
    @endif
</div>
@endforeach
