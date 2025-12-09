<div class="flex flex-col md:flex-row md:items-start gap-6 md:gap-8 text-black">

    {{-- Left column: Card Image --}}
    <div class="md:w-1/2 flex-shrink-0 flex items-center justify-center">
        @php
        $img = data_get($card, 'card_images.0.image_url_small')
        ?: data_get($card, 'card_images.0.image_url')
        ?: data_get($card, 'image_url');
        @endphp

        @if($img)
        <img src="{{ $img }}" alt="{{ $card->name }}" class="w-full max-w-md rounded-lg shadow-lg bg-black object-contain">
        @else
        <div class="w-full max-w-xs h-56 rounded-lg bg-gray-800 flex items-center justify-center">
            <span class="text-slate-400">No image available</span>
        </div>
        @endif
    </div>

    {{-- Right column: Card Details --}}
    <div class="md:flex-1">

        {{-- Card Name --}}
        <h1 class="text-2xl font-bold mb-2 text-black">{{ $card->name }}</h1>

        {{-- Type / Race / Archetype --}}
        <div class="text-sm text-slate-700 mb-3">
            @php
            $types = [];
            if (!empty($card->typeline)) {
            if (is_array($card->typeline)) $types = $card->typeline;
            elseif (is_string($card->typeline)) $types = explode('/', $card->typeline);
            }
            $types = array_filter(array_map('trim', array_slice($types, 1)));
            @endphp

            <span>Type: <strong class="text-black">{{ !empty($types) ? implode('/', $types) : ($card->type ?? 'Unknown') }}</strong></span>
            <span class="mx-2">•</span>
            <span>Race: <strong class="text-black">{{ $card->race ?? 'Unknown' }}</strong></span>
            <span class="mx-2">•</span>
            <span>Archetype: <strong class="text-black">{{ $card->archetype ?? 'Unknown' }}</strong></span>
        </div>

        {{-- Stats --}}
        <div class="flex flex-wrap gap-3 mb-4">
            @if(!empty($card->atk))
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">ATK: {{ $card->atk ?? '-' }}</div>
            @if(empty($card->def))
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">Link: {{ $card->linkval ?? 'Unknown' }}</div>
            @else
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">DEF: {{ $card->def ?? '-' }}</div>
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">Level: {{ $card->level ?? 'Unknown' }}</div>
            @endif
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">Attribute: {{ $card->attribute ?? 'Unknown' }}</div>
            @else
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">Price: ${{ $card->card_prices[0]->cardmarket_price ?? 'Unknown' }}</div>
            @endif
        </div>

        {{-- Description --}}
        <div class="prose prose-invert text-black mb-4">
            <p>{{ $card->desc ?? 'No description available' }}</p>
        </div>

        {{-- Prints --}}
        <div class="mb-4">
            <h3 class="text-lg font-semibold mb-2 text-black">Prints</h3>
            <x-print-list :sets="$card->card_sets" id="prints-{{ $card->id }}" />
        </div>

        {{-- More from this archetype --}}
        @if(!empty($archetypeCards) && $archetypeCards->count())
        <div class="mt-6  border-slate-700 p-6 ">
            <h3 class="text-lg text-black font-semibold mb-3">More from this Archetype</h3>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                @foreach($archetypeCards as $s)
                @php
                $img = data_get($s, 'card_images.0.image_url_small')
                ?: data_get($s,'image_uris.normal')
                ?: data_get($s,'image');
                $link = isset($s['id']) ? url('yugioh/card/'.$s['id']) : '#';
                $name = data_get($s,'name') ?: data_get($s,'card_name') ?: 'Card';
                @endphp
                <a href="{{ $link }}" class="w-full hover:scale-105 transform transition" aria-label="{{ $name }}">
                    <img src="{{ $img }}" loading="lazy" alt="{{ $name }}" class="w-full h-auto rounded shadow bg-black object-cover">
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="mt-6 flex gap-3">
            <a href="/yugioh/cards" class="inline-block bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded">Back to Database</a>
            <a href="#" class="inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded">Add to Deck</a>
        </div>

    </div>
</div>