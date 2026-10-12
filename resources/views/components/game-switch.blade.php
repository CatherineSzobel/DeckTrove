@props(['series'])

{{-- Switches to the same section in another game: cards → cards, packs → packs, deck builder → deck builder. --}}
@php
    $route = request()->route()?->getName() ?? '';
    $target = match (true) {
        str_starts_with($route, 'packs.') => 'packs.index',
        $route === 'decks.builder' => 'decks.builder',
        default => 'cards.index',
    };
@endphp

<div role="group" aria-label="Game" data-game-switch class="inline-flex flex-wrap gap-1 rounded-xl border border-line bg-surface p-1">
    @foreach (config('series') as $key => $config)
    <a href="{{ route($target, $key) }}" data-game="{{ $key }}" @if ($key === $series) aria-current="page" @endif class="segment">{{ $config['label'] }}</a>
    @endforeach
</div>
