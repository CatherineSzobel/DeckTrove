<div class="flex flex-col md:flex-row md:items-start gap-6 md:gap-8 text-black">
    <div class="md:w-1/2 flex-shrink-0 flex items-center justify-center">
        @php
        $img = data_get($card, 'card_images.0.image_url_small') ?: data_get($card, 'card_images.0.image_url') ?: data_get($card, 'image_url') ;
        @endphp

        @if($img)
        <img src="{{ $img }}" alt="{{ $card->name }}" class="w-full max-w-md rounded-lg shadow-lg bg-black object-contain">
        @else
        <div class="w-full max-w-xs h-56 rounded-lg bg-gray-800 flex items-center justify-center">
            <span class="text-slate-400">No image available</span>
        </div>
        @endif
    </div>

    <div class="md:flex-1">
        <h1 class="text-2xl font-bold mb-2 text-black">{{ $card->name }}</h1>
        <div class="text-sm text-slate-700 mb-3">
            @php
            // Ensure $types is always an array
            $types = is_array($card->typeline)
            ? $card->typeline
            : (is_string($card->typeline) ? explode('/', $card->typeline) : []);

            // Ignore the first element
            $types = array_slice($types, 1);
            @endphp

            @if (!empty($types))
                <span>
                    Type:
                    <strong class="text-black">
                        {{ implode('/', array_map(fn($t) => trim($t), $types)) ?: 'Unknown' }}
                    </strong>
                </span>
            @else
                <span>Type: <strong class="text-black">{{ $card->type ?? 'Unknown' }}</strong></span>
            @endif

            <span class="mx-2">•</span>
            <span>Race: <strong class="text-black">{{ $card->race ?? 'Unknown' }}</strong></span>
            <span class="mx-2">•</span>
            <span>Archetype: <strong class="text-black">{{ $card->archetype ?? 'Unknown' }}</strong></span>
        </div>

        <div class="flex flex-wrap gap-3 mb-4">
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">ATK: {{ $card->atk ?? '-' }}</div>
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">DEF: {{ $card->def ?? '-' }}</div>
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">Price: {{ $card->card_prices[0]->cardmarket_price ?? 'Unknown' }}</div>
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">Level: {{ $card->level ?? 'Unknown' }}</div>
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">Attribute: {{ $card->attribute ?? 'Unknown' }}</div>
        </div>

        <div class="prose prose-invert text-black mb-4">
            <p>{{ $card->desc ?? 'No description available' }}</p>
        </div>

        <div class="mb-4">
            <h3 class="text-lg font-semibold mb-2 text-black">Prints</h3>

            @if(!empty($card->card_sets))
            @php // Map card sets to pack codes (trim after dash) and ensure unique packs
            $uniqueSets = collect($card->card_sets) ->map(function($set) { $setCodeRaw = $set->set_code ?? '';
            $packCode = explode('-', $setCodeRaw)[0] ?? $setCodeRaw;
            return (object) [ 'pack_code' => $packCode, 'set_name' => $set->set_name ?? 'Unknown' ]; }) ->unique('pack_code');
            @endphp
            <ul class="list-inside list-disc text-blue-400 font-bold">
                @foreach($uniqueSets as $set)
                <li>
                    @if($set->pack_code)
                    <a href="{{ url('/yugioh/pack/' . urlencode($set->pack_code)) }}" class=" hover:text-blue-800">
                        {{ $set->set_name }}

                    </a>
                    @else
                    <span>
                        {{ $set->set_name }}
                    </span>
                    @endif
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-slate-200">No prints available</p>
            @endif
        </div>

        @if(!empty($setCards) && $setCards->count())
        <div class="mt-6 border-t border-slate-700 p-6 bg-slate-500 rounded-lg">

            {{-- Tabs Container --}}
            <div class="tab-container rounded-lg">

                {{-- Tab Buttons --}}
                <div class="tab-list flex flex-wrap gap-2 mb-4">
                    <button type="button" class="tab-link bg-black relative flex flex-wrap gap-2 items-center px-3 py-2 rounded-lg text-white" data-tab-target="set" aria-selected="false">
                        More from this set
                    </button>
                    <button type="button" class="tab-link bg-black relative flex flex-wrap gap-2 items-center px-3 py-2 rounded-lg text-white" data-tab-target="archetypes" aria-selected="false">
                        More from this archetype
                    </button>
                </div>

                {{-- Tab Panels --}}
                <div class="tab-content mt-4">

                    {{-- Set Panel --}}
                    <div id="set" class="tab-panel hidden">
                        <h3 class="text-lg text-black font-semibold mb-3">More from this set</h3>
                        @php
                        $thumbsId = 'set-thumbs-yugioh-'.(data_get($card,'id') ?? substr(data_get($card,'name') ?? '',0,8));
                        @endphp
                        <div id="{{ $thumbsId }}" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            @foreach($setCards as $s)
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

                    {{-- Archetypes Panel --}}
                    <div id="archetypes" class="tab-panel hidden">
                        <h3 class="text-lg text-black font-semibold mb-3">More from this archetype</h3>
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            @if($archetypeCards->count() <= 0)
                                <p class="text-slate-200">No cards found</p>
                                @else
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
                                @endif

                        </div>
                    </div>

                </div>
            </div>
        </div>
        @endif


        <div class="mt-6 flex gap-3">
            <a href="/yugioh/cards" class="inline-block bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded">Back to Database</a>
            <a href="#" class="inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded">Add to Deck</a>
        </div>
    </div>
</div>