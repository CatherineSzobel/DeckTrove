<x-layout :title="$deck->name" :js="['resources/js/deck.js']">
    <div class="max-w-6xl mx-auto px-4 py-10">
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900">{{ $deck->name }}</h1>
            @if ($deck->description)
            <p class="text-sm md:text-base text-gray-500 mt-2">{{ $deck->description }}</p>
            @endif
            <p class="text-sm text-gray-500 mt-2">
                {{ config("series.{$deck->game}.label") }} · by {{ $deck->user->username }} ·
                <span class="font-semibold text-amber-500">{{ $deck->card_count }}</span> cards
                @unless ($deck->is_public) · <span class="font-semibold">Private</span> @endunless
            </p>

            @can('update', $deck)
            <div class="mt-4 flex justify-center gap-3">
                <a href="{{ route('decks.edit', $deck) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">Edit deck</a>
            </div>
            @endcan
        </div>

        @foreach ($zones as $zone => $zoneRules)
        @continue(empty($sections[$zone]) || $sections[$zone]->isEmpty())

        <section class="mb-10">
            <button type="button" data-zone-toggle="{{ $zone }}" aria-expanded="true" aria-controls="{{ $zone }}-cards"
                class="w-full flex items-center justify-between bg-gray-100 p-3 rounded-lg shadow-sm hover:bg-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">{{ $zoneRules['label'] }}</h2>
                <span class="flex items-center space-x-2">
                    <span class="text-gray-500 text-sm">{{ $sections[$zone]->count() }} cards</span>
                    <span data-toggle-icon class="px-2 py-1 bg-gray-200 rounded text-sm text-gray-700" aria-hidden="true">−</span>
                </span>
            </button>

            <div id="{{ $zone }}-cards" class="mt-4 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
                @foreach ($sections[$zone] as $card)
                <div class="relative group inline-block">
                    <a href="{{ $card->link() }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ $card->imageSmall() }}" alt="{{ $card->name() }}" loading="lazy"
                            class="w-20 h-auto rounded-sm shadow-sm cursor-pointer transition-transform duration-200 hover:scale-105">
                    </a>

                    <div class="absolute z-50 hidden group-hover:block w-80 p-4 bg-slate-900 text-white rounded-lg shadow-lg border border-slate-700 -top-2 left-full ml-2 pointer-events-none">
                        <div class="flex space-x-3">
                            <img src="{{ $card->imageSmall() }}" alt="" class="w-24 h-auto rounded-sm border border-slate-600">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">{{ $card->name() }}</h3>
                                <p class="text-sm text-slate-300 mt-1">{{ trim($card->type().' '.$card->subtype()) }}</p>
                                @if ($card->hasStats())
                                <p class="text-sm mt-1">{{ $card->statLine() }}</p>
                                @endif
                                <p class="text-xs mt-2 text-slate-200 line-clamp-6">{{ $card->description() ?: 'No description' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endforeach

        @if (collect($sections)->every(fn ($cards) => $cards->isEmpty()))
        <div class="mt-12 text-center text-gray-500">No cards in this deck yet.</div>
        @endif
    </div>
</x-layout>
