@php
    $repo = 'https://github.com/CatherineSzobel/DeckTrove';
    $columns = [
        'Series' => collect(config('series'))->map(fn ($config, $series) => [$config['label'], route('cards.index', $series)])->values()->all(),
        'Community' => [
            ['Public Decks', route('public-deck')],
            ['Create a Deck', route('decks.builder', session('series', array_key_first(config('series'))))],
        ],
        'Resources' => collect(config('series'))->map(fn ($config, $series) => ["{$config['label']} packs", route('packs.index', $series)])->values()->all(),
        'Project' => [
            ['Project Overview', $repo.'#readme'],
            ['Architecture', $repo.'#architecture--design-decisions'],
            ['Source Code', $repo],
        ],
    ];
@endphp

<footer class="bg-white">
    <div class="mx-auto max-w-7xl space-y-8 px-4 py-16 sm:px-6 lg:space-y-16 lg:px-8">
        <div class="sm:flex sm:items-center sm:justify-between">
            <img src="{{ Vite::asset('resources/img/decktrove-logo.png') }}" alt="DeckTrove" class="h-24 w-24 mt-2" />

            <a href="{{ $repo }}" rel="noreferrer" target="_blank" class="mt-8 sm:mt-0 inline-block text-gray-700 transition hover:opacity-75">
                <span class="sr-only">DeckTrove on GitHub</span>
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-8 border-t border-gray-100 pt-8 sm:grid-cols-2 lg:grid-cols-4 lg:pt-16">
            @foreach ($columns as $heading => $links)
            <div>
                <p class="font-medium text-gray-900">{{ $heading }}</p>
                <ul class="mt-6 space-y-4 text-sm">
                    @foreach ($links as [$label, $href])
                    <li><a href="{{ $href }}" class="text-gray-700 transition hover:opacity-75">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        <div class="space-y-2 text-xs text-gray-500">
            <p>© {{ date('Y') }} DeckTrove. All rights reserved.</p>

            {{-- Wording required by the Wizards of the Coast Fan Content Policy. --}}
            <p>DeckTrove is unofficial Fan Content permitted under the Fan Content Policy. Not approved/endorsed by Wizards. Portions of the materials used are property of Wizards of the Coast. ©Wizards of the Coast LLC.</p>
            <p>Yu-Gi-Oh! is a trademark of Konami Digital Entertainment. DeckTrove is not affiliated with or endorsed by Konami.</p>
            <p>Card data from <a href="https://scryfall.com" class="underline hover:text-gray-700" rel="noreferrer" target="_blank">Scryfall</a> and <a href="https://ygoprodeck.com" class="underline hover:text-gray-700" rel="noreferrer" target="_blank">YGOPRODeck</a>.</p>
        </div>
    </div>
</footer>
