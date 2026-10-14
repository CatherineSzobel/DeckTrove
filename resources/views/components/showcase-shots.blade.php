@props(['alts' => ['DeckTrove card database', 'DeckTrove deck builder']])

{{-- Two tilted screenshots on a soft accent glow (log in / create account showcase, home page hero). --}}
<div {{ $attributes->class('relative aspect-[5/3.6]') }}>
    <div class="showcase-glow" aria-hidden="true"></div>
    <img src="{{ Vite::asset('resources/img/screenshots/card-database.png') }}" alt="{{ $alts[0] }}" data-shot="1" class="showcase-shot">
    <img src="{{ Vite::asset('resources/img/screenshots/deck-builder.png') }}" alt="{{ $alts[1] }}" data-shot="2" class="showcase-shot">
</div>
