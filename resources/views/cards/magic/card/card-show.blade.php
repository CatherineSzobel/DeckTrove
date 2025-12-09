<div class="flex flex-col md:flex-row md:items-start gap-6 md:gap-8 text-black">

    <!-- LEFT: Card Image -->
    <div class="md:w-1/2 flex-shrink-0 flex items-center justify-center">
        @php
        $img = data_get($card, 'image_uris.normal')
        ?: data_get($card, 'card_faces.0.image_uris.normal')
        ?: data_get($card, 'image_url');
        @endphp

        @if($img)
        <img src="{{ $img }}"
            alt="{{ $card['name'] ?? data_get($card,'name') }}"
            class="w-full max-w-md rounded-lg shadow-lg bg-black object-contain">
        @else
        <div class="w-full max-w-xs h-56 rounded-lg bg-gray-800 flex items-center justify-center">
            <span class="text-slate-400">No image available</span>
        </div>
        @endif
    </div>


    <!-- RIGHT: Info Panel -->
    <div class="md:flex-1">

        <h1 class="text-2xl font-bold mb-2 text-black">
            {{ $card['name'] ?? data_get($card,'name','Unknown Card') }}
        </h1>

        <div class="text-sm text-slate-200 mb-3">
            <span class="mr-2 text-black">
                Type:
                <strong class="text-black">
                    {{ $card['type_line'] ?? 'Unknown Type' }}
                </strong>
            </span>
        </div>

        <div class="flex flex-wrap gap-3 mb-4">
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">
                Price: {{ data_get($card,'prices.usd') ?? data_get($card,'prices.eur') ?? 'Unknown' }}
            </div>

            @if(data_get($card,'mana_cost'))
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">
                Mana: {{ data_get($card,'mana_cost') }}
            </div>
            @endif

            @if(data_get($card,'rarity'))
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">
                Rarity: {{ data_get($card,'rarity') }}
            </div>
            @endif
        </div>

        <div class="prose prose-invert text-black mb-4">
            <p>
                {!! nl2br(e(
                data_get($card,'oracle_text')
                ?? data_get($card,'card_faces.0.oracle_text')
                ?? 'No description available'
                )) !!}
            </p>
        </div>

        <div class="mb-4">
            <h3 class="text-lg font-semibold mb-2 text-black">Printings</h3>
            <x-print-list :sets="[ (object)['set_code' => $card['set'] ?? '',
            'set_name' => $card['set_name'] ?? 'Unknown']]"
                id="prints-{{ $card['id'] ?? 'default' }}" tcg-game="magic" />
        </div>

        @if(!empty($setCards) && $setCards->count())
        <div class="mt-6  border-slate-700 p-6 ">
            <h3 class="text-lg text-black font-semibold mb-3">More from this set</h3>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">

                @foreach($setCards as $s)
                @php
                $img = data_get($s,'image_uris.normal')
                ?: data_get($s,'card_faces.0.image_uris.normal')
                ?: data_get($s,'image_url');

                $link = isset($s['id']) ? url('magic/card/'.$s['id']) : '#';
                $name = data_get($s,'name','Card');
                @endphp

                <a href="{{ $link }}"
                    class="hover:scale-105 transform transition block"
                    aria-label="{{ $name }}">
                    <div class="aspect-[3/4] w-full rounded shadow bg-black">
                        <img src="{{ $img }}"
                            alt="{{ $name }}"
                            loading="lazy"
                            class="w-full h-full object-contain rounded">
                    </div>
                </a>
                @endforeach

            </div>
        </div>
        @endif

        <div class="mt-6 flex gap-3">
            <a href="/magic/cards" class="inline-block bg-slate-700 hover:bg-slate-600 text-white font-bold px-4 py-2 rounded">Back to Database</a>
            <a href="{{ url('/magic/deck-builder') }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded font-bold"> Go to deckbuilder</a>
        </div>

    </div>

</div>