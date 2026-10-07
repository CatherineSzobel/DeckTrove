@php $faces = $card->faceImages(); @endphp

<div class="flex flex-col md:flex-row md:items-start gap-6 md:gap-8 text-gray-900">
    <div class="md:w-1/2 flex-shrink-0 flex items-center justify-center md:flex-1 flex-col">
        <img id="cardImage" src="{{ $card->image() }}" alt="{{ $card->name() }}"
            @if (count($faces) > 1) data-faces="{{ json_encode($faces) }}" @endif
            class="w-84 max-w-md rounded-lg shadow-lg bg-black object-contain">

        @if (count($faces) > 1)
        <button type="button" id="transformButton"
            class="mt-4 px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Transform
        </button>
        @endif
    </div>

    <div class="md:flex-1">
        <h1 class="text-2xl font-bold mb-2 text-gray-900">{{ $card->name() }}</h1>

        <div class="text-sm text-gray-800 mb-3">
            Type: <strong>{{ $card->type() ?: '-' }}</strong>
            @if ($card->subtype())
            — <span>{{ $card->subtype() }}</span>
            @endif
        </div>

        <div class="flex flex-wrap gap-3 mb-4">
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">Price: {{ $card->price() }}</div>

            @if ($card->hasStats())
            <div class="bg-slate-700 text-slate-200 px-3 py-1 rounded">{{ $card->statLine() }}</div>
            @endif
        </div>

        <div class="text-gray-900 mb-4 whitespace-pre-line">{{ $card->description() }}</div>

        <div class="mb-4">
            <h3 class="text-lg font-semibold mb-2 text-gray-900">Printings</h3>
            <x-print-list :sets="$card->printSets()" :series="$series" id="prints-{{ $card->id() ?? 'default' }}" />
        </div>

        @if ($setCards->isNotEmpty())
        <div class="mt-6 border-slate-700 p-6">
            <h3 class="text-lg text-gray-900 font-semibold mb-3">{{ $series === 'magic' ? 'More from this set' : 'More from this archetype' }}</h3>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                @foreach ($setCards as $setCard)
                <a href="{{ $setCard->link() }}" class="hover:scale-105 transform transition block" aria-label="{{ $setCard->name() }}">
                    <div class="aspect-[3/4] w-full rounded shadow bg-black">
                        <img src="{{ $setCard->imageSmall() }}" alt="{{ $setCard->name() }}" loading="lazy" class="w-full h-full object-contain rounded">
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mt-6 flex gap-3">
            <a href="{{ route('cards.index', $series) }}" class="inline-block bg-slate-700 hover:bg-slate-600 text-white font-bold px-4 py-2 rounded">Back to Database</a>
            <a href="{{ route('decks.builder', $series) }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded font-bold">Go to Deckbuilder</a>
        </div>
    </div>
</div>
