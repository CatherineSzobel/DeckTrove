{{-- A saved deck: header with format and price, format problems, the zones, the comments and the deck stats sidebar. --}}

<x-layout :title="$deck->name" :js="['resources/js/deck-page.js']">
    <div x-data="cardPreview" @scroll.window.passive="hidePreview()" class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-muted">
            @if ($isOwner)
            <a href="{{ route('decks.mine') }}" class="focus-ring text-body hover:text-ink">My decks</a>
            @else
            <a href="{{ route('decks.index') }}" class="focus-ring text-body hover:text-ink">Public decks</a>
            @endif
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-ink">{{ $deck->name }}</span>
        </nav>

        <section class="flex flex-wrap items-center gap-10 rounded-3xl border border-line bg-surface p-6 sm:p-8">
            <x-deck.cover-fan :deck="$deck" size="lg" />

            <div class="flex min-w-0 flex-[1_1_420px] flex-col gap-4">
                <p class="eyebrow">{{ $deck->series_label }} deck · by <a href="{{ route('users.show', $deck->user) }}" class="focus-ring rounded-sm underline underline-offset-4 hover:text-ink">{{ $deck->user->username }}</a></p>
                <div class="flex flex-wrap items-center gap-4">
                    <h1 class="heading-display text-[clamp(44px,5vw,68px)] text-ink">{{ $deck->name }}</h1>
                    <x-deck.format-badge :format="$deck->format" :label="$deck->format_label" :problems="count($formatErrors['messages'])" />
                </div>
                @if ($deck->description)
                <p class="max-w-[44rem] text-[17px] leading-relaxed text-body">{{ $deck->description }}</p>
                @endif

                <div class="flex flex-wrap gap-2.5">
                    <x-stat-chip :value="$deck->card_count" label="cards" accent inset />
                    @foreach ($stats->headlineCounts() as $value => $count)
                    <x-stat-chip :value="$count" :label="Str::plural(strtolower($value), $count)" inset />
                    @endforeach
                    <x-stat-chip :value="$stats->money($stats->price)" label="deck price" warn inset data-chip="price"
                        :title="$stats->missingPrices ? $stats->missingPrices.' '.Str::plural('card', $stats->missingPrices).' have no price' : null" />
                </div>
            </div>

            <div class="flex min-w-[200px] flex-[0_1_auto] flex-col gap-2.5">
                @if ($isOwner)
                <a href="{{ route('decks.edit', $deck) }}" class="btn-primary">
                    <x-heroicon-o-pencil-square class="h-5 w-5" aria-hidden="true" />
                    Edit deck
                </a>
                @else
                {{-- Other players toggle their like; guests get a link to log in. --}}
                <x-deck.like-button :deck="$deck" :liked="$liked" :count="$deck->likes_count" size="lg" />
                @auth
                <form method="POST" action="{{ route('decks.copy', $deck) }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full">
                        <x-heroicon-o-document-duplicate class="h-5 w-5" aria-hidden="true" />
                        Copy to my decks
                    </button>
                </form>
                @endauth
                @endif
                <a href="{{ route('decks.export', $deck) }}" class="btn-secondary">
                    <x-heroicon-o-arrow-down-tray class="h-5 w-5" aria-hidden="true" />
                    Export .{{ $exportExtension }}
                </a>
                @if ($isOwner && $deck->is_public)
                <x-deck.like-button :deck="$deck" :liked="false" :count="$deck->likes_count" size="lg" />
                @endif
                @if ($comments->visible())
                <a href="#comments" data-comments-link class="focus-ring flex min-h-11 items-center justify-center gap-2 rounded-[10px] px-3 text-sm font-bold text-accent-ink hover:text-ink">
                    <x-heroicon-o-chat-bubble-left-right class="h-5 w-5" aria-hidden="true" />
                    <span>{{ $comments->headerLabel() }}</span>
                </a>
                @endif
                @unless ($deck->is_public)
                <span class="text-center text-xs text-muted">Private: only you can see this deck</span>
                @endunless
            </div>
        </section>

        @if ($formatErrors['messages'])
        <x-deck.problems :title="'This deck no longer fits '.$deck->format_label" :messages="$formatErrors['messages']">
            @if ($isOwner)
            <a href="{{ route('decks.edit', $deck) }}" class="focus-ring font-bold text-accent-ink">Edit the deck</a> to fix it, or switch it to Casual.
            @else
            The owner can fix it or switch it to Casual.
            @endif
        </x-deck.problems>
        @endif

        <div class="flex flex-wrap items-start gap-6">
            <div class="flex min-w-0 flex-[999_1_640px] flex-col gap-8">
                @foreach ($zones as $zone => $zoneRules)
                @continue(empty($sections[$zone]) || $sections[$zone]->isEmpty())

                <section x-data="{ open: true }" class="flex flex-col gap-4">
                    <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="{{ $zone }}-cards"
                        class="focus-ring flex min-h-14 w-full items-center justify-between gap-3 rounded-[14px] border border-line bg-surface px-5 text-ink">
                        <h2 class="heading-display text-[26px]">{{ $zoneRules['label'] }}</h2>
                        <span class="flex items-center gap-3">
                            <span class="font-mono text-sm font-bold">{{ $sections[$zone]->count() }} cards<span class="font-medium text-muted"> · {{ $stats->money($stats->zone($zone)['price']) }}</span></span>
                            <x-heroicon-o-chevron-up class="h-5 w-5 transition-transform" x-bind:class="open || 'rotate-180'" aria-hidden="true" />
                        </span>
                    </button>

                    <div id="{{ $zone }}-cards" x-show="open" class="grid grid-cols-[repeat(auto-fill,minmax(88px,1fr))] gap-3">
                        @foreach ($sections[$zone] as $card)
                        <x-deck.card-slot :card="$card" :href="$card->link()" :over="$cardProblems[$card->id()] ?? null"
                            :x-data="'{ card: '.Js::from($card->builderCard()).' }'"
                            x-on:mouseenter="showPreview(card, $el)" x-on:mouseleave="hidePreview()"
                            x-on:focus="showPreview(card, $el)" x-on:blur="hidePreview()" />
                        @endforeach
                    </div>
                </section>
                @endforeach

                @if (collect($sections)->every(fn ($cards) => $cards->isEmpty()))
                <x-empty-state title="No cards in this deck yet" />
                @endif

                @if ($comments->visible())
                @include('decks.partials.comments')
                @endif
            </div>

            @include('decks.partials.stats-sidebar')
        </div>

        <x-deck.card-preview />
    </div>
</x-layout>
