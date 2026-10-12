{{-- A saved deck: header with format and price, format problems, the zones and the deck stats sidebar. --}}
@php
    $series = config("series.{$deck->game}");
    $format = $deck->format;
    $formatLabel = $format ? ($series['deck']['formats'][$format]['label'] ?? $format) : null;
    $overMessages = array_combine($formatErrors['card_ids'], $formatErrors['messages']);
    $isOwner = auth()->user()?->can('update', $deck) ?? false;
    $extension = app($series['deck_format'])->extension();
    $euro = fn (float $value) => $stats->currency['symbol'].number_format($value, 2);

    // The first stats group (e.g. Monster / Spell / Trap) as chips for the main deck.
    $firstGroup = array_key_first($series['deck']['stats']);
    $chips = collect($stats->zone('main')['groups'][$firstGroup]['values'] ?? [])->filter();
@endphp

<x-layout :title="$deck->name" :js="['resources/js/deck-page.js']">
    <div x-data="cardPreview" @scroll.window.passive="hidePreview()" class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-muted">
            @if ($isOwner)
            <a href="{{ route('decks') }}" class="focus-ring text-body hover:text-ink">My decks</a>
            @else
            <a href="{{ route('public-deck') }}" class="focus-ring text-body hover:text-ink">Public decks</a>
            @endif
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-ink">{{ $deck->name }}</span>
        </nav>

        <section class="flex flex-wrap items-center gap-10 rounded-3xl border border-line bg-surface p-6 sm:p-8">
            <div class="relative h-[210px] w-[220px] flex-none" aria-hidden="true">
                <div class="absolute left-[22px] top-[34px] aspect-[59/86] w-[120px] -rotate-12 rounded-lg bg-surface-2 shadow-lg"></div>
                <div class="absolute left-[82px] top-[34px] aspect-[59/86] w-[120px] rotate-[10deg] rounded-lg bg-surface-2 shadow-lg"></div>
                @if ($deck->image)
                <img src="{{ $deck->image }}" alt="" class="absolute left-[52px] top-3 aspect-[59/86] w-[120px] rounded-lg object-cover shadow-xl">
                @else
                <div class="absolute left-[52px] top-3 aspect-[59/86] w-[120px] rounded-lg border border-line-strong bg-canvas shadow-xl"></div>
                @endif
            </div>

            <div class="flex min-w-0 flex-[1_1_420px] flex-col gap-4">
                <p class="eyebrow">{{ $series['label'] }} deck · by <a href="{{ route('users.show', $deck->user) }}" class="focus-ring rounded-sm underline underline-offset-4 hover:text-ink">{{ $deck->user->username }}</a></p>
                <div class="flex flex-wrap items-center gap-4">
                    <h1 class="heading-display text-[clamp(44px,5vw,68px)] text-ink">{{ $deck->name }}</h1>
                    <x-deck.format-badge :format="$format" :label="$formatLabel" :problems="count($formatErrors['messages'])" />
                </div>
                @if ($deck->description)
                <p class="max-w-[44rem] text-[17px] leading-relaxed text-body">{{ $deck->description }}</p>
                @endif

                <div class="flex flex-wrap gap-2.5">
                    <span class="inline-flex items-baseline gap-2 rounded-[10px] border border-line bg-canvas px-3 py-2 text-[13px] text-body">
                        <span class="font-mono text-base font-bold text-accent-ink">{{ $deck->card_count ?? collect($sections)->sum(fn ($cards) => $cards->count()) }}</span>cards
                    </span>
                    @foreach ($chips as $value => $count)
                    <span class="inline-flex items-baseline gap-2 rounded-[10px] border border-line bg-canvas px-3 py-2 text-[13px] text-body">
                        <span class="font-mono text-base font-bold text-ink">{{ $count }}</span>{{ Str::plural(strtolower($value), $count) }}
                    </span>
                    @endforeach
                    <span data-chip="price" @if ($stats->missingPrices) title="{{ $stats->missingPrices }} {{ Str::plural('card', $stats->missingPrices) }} have no price" @endif
                        class="inline-flex items-baseline gap-2 rounded-[10px] border border-warn-line bg-canvas px-3 py-2 text-[13px] text-body">
                        <span class="font-mono text-base font-bold text-ink">{{ $euro($stats->price) }}</span>deck price
                    </span>
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
                    Export .{{ $extension }}
                </a>
                @if ($isOwner && $deck->is_public)
                <x-deck.like-button :deck="$deck" :liked="false" :count="$deck->likes_count" size="lg" />
                @endif
                @unless ($deck->is_public)
                <span class="text-center text-xs text-muted">Private: only you can see this deck</span>
                @endunless
            </div>
        </section>

        @if ($formatErrors['messages'])
        <x-deck.problems :title="'This deck no longer fits '.$formatLabel" :messages="$formatErrors['messages']">
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
                            <span class="font-mono text-sm font-bold">{{ $sections[$zone]->count() }} cards<span class="font-medium text-muted"> · {{ $euro($stats->zone($zone)['price']) }}</span></span>
                            <x-heroicon-o-chevron-up class="h-5 w-5 transition-transform" x-bind:class="open || 'rotate-180'" aria-hidden="true" />
                        </span>
                    </button>

                    <div id="{{ $zone }}-cards" x-show="open" class="grid grid-cols-[repeat(auto-fill,minmax(88px,1fr))] gap-3">
                        @foreach ($sections[$zone] as $card)
                        <x-deck.card-slot :card="$card" :href="$card->link()" :over="$overMessages[$card->id()] ?? null"
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
            </div>

            @include('decks.partials.stats-sidebar')
        </div>

        <x-deck.card-preview />
    </div>
</x-layout>
