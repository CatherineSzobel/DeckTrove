{{-- One card inside a deck zone. Mirrors createDeckCard() in resources/js/deckbuilder/helpers.js. --}}
<div class="relative w-20 h-28 mb-2 mx-auto group card-wrapper cursor-pointer" draggable="true"
    @foreach ($card->deckBuilderData() as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>
    <img src="{{ $card->imageSmall() }}" alt="{{ $card->name() }}" class="w-full h-full object-cover rounded card">

    <div class="absolute inset-0 bg-black/70 text-white opacity-0 group-hover:opacity-100 transition-opacity rounded p-1 flex flex-col justify-center items-center text-center">
        <h3 class="font-bold text-[10px] leading-snug">{{ $card->name() }}</h3>
        <p class="text-[8px] mt-1 pointer-events-none">{{ $card->type() ?: $card->subtype() }}</p>
    </div>
</div>
