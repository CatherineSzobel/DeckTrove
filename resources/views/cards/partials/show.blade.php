<div class="flex flex-col md:flex-row md:items-start gap-6 md:gap-8 text-black">

    <div class="md:w-1/2 flex-shrink-0 flex items-center justify-center md:flex-1 flex-col">
        @if($card->image())
        <img src="{{ $card->image() }}"
            alt="{{ $card->name() }}"
            class="w-84 max-w-md rounded-lg shadow-lg bg-black object-contain">
        @else
        <div class="w-full max-w-xs h-56 rounded-lg bg-gray-800 flex items-center justify-center">
            <span class="text-slate-400">No image available</span>
        </div>
        @endif

        @if($series === 'magic' && $card->isMultiFace())
        <button id="toggleImageButton"
            class="mt-4 px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
            onclick="toggleCardImageSize()">
            Transform
        </button>
        @endif
    </div>

    <div class="md:flex-1">
        <h1 class="text-2xl font-bold mb-2 text-black">{{ $card->name() }}</h1>

        <div class="text-sm text-slate-800 mb-3">
            <span class="mr-2 text-black">
                Type: <strong>{{ $card->type()}}</strong>
                @if($card->subtype())
                — <span>{{ $card->subtype() }}</span>
                @endif
            </span>
        </div>

        <div class="flex flex-wrap gap-3 mb-4">
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">
                Price: {{ $card->price() }}
            </div>

            @if($card->stats()['left']['value'] !== null && $card->stats()['right']['value'] !== null)
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">
                {{ $series === 'magic' ? 'Power' : 'ATK' }}: {{ $card->stats()['left']['value'] ?? null }} |
                {{ $series === 'magic' ? 'Toughness' : 'DEF' }}: {{ $card->stats()['right']['value'] }}
            </div>
            @endif
        </div>

        <div class="prose prose-invert text-black mb-4">
            {!! nl2br(e($card->description())) !!}
        </div>

        <div class="mb-4">
            <h3 class="text-lg font-semibold mb-2 text-black">Printings</h3>
            <x-print-list :sets="$card->printSets()"
                id="prints-{{ $card->id() ?? 'default' }}"
                :tcg-game="$series" />
        </div>

        @if(!empty($setCards) && $setCards->count())
        <div class="mt-6 border-slate-700 p-6">
            <h3 class="text-lg text-black font-semibold mb-3">More from this set</h3>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                @foreach($setCards as $setCard)

                <a href="{{ $setCard->link() }}" class="hover:scale-105 transform transition block" aria-label="{{ $setCard->name() }}">
                    <div class="aspect-[3/4] w-full rounded shadow bg-black">
                        @if( $setCard->image() !== null)
                        <img src="{{ $setCard->image() }}"
                            alt="{{ $setCard->name() }}"
                            loading="lazy"
                            class="w-full h-full object-contain rounded">
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-800 rounded">
                            <span class="text-slate-400">No image</span>
                        </div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mt-6 flex gap-3">
            <a href="{{ url("/{$series}/cards") }}" class="inline-block bg-slate-700 hover:bg-slate-600 text-white font-bold px-4 py-2 rounded">
                Back to Database
            </a>
            <a href="{{ url("/{$series}/deck-builder") }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded font-bold">
                Go to Deckbuilder
            </a>
        </div>
    </div>
</div>