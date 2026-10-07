@props(['series' => null, 'seriesLabels' => [], 'comingSoon' => [], 'mobile' => false])

{{-- Navigation shared by the desktop bar and the mobile menu. Links point at the series being browsed. --}}
@php
    $link = fn (string $route) => $series ? route($route, $series) : route('index');
@endphp

<select aria-label="Select series" data-series-select
    class="rounded border border-slate-600 bg-slate-700 px-2 py-1 text-sm text-slate-200 {{ $mobile ? 'w-full mb-2' : '' }}">
    <option value="" @selected(! $series) disabled>Select series</option>
    @foreach ($seriesLabels as $value => $label)
    <option value="{{ route('cards.index', $value) }}" @selected($series === $value)>{{ $label }}</option>
    @endforeach
    @foreach ($comingSoon as $label)
    <option disabled>{{ $label }} (soon)</option>
    @endforeach
</select>

@if ($mobile)
<div class="flex flex-col gap-1">
    @auth
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Home</x-nav-link>
    @endauth
    <x-nav-link :href="$link('cards.index')" :active="request()->routeIs('cards.*')">Cards database</x-nav-link>
    <x-nav-link :href="$link('packs.index')" :active="request()->routeIs('packs.*')">Packs</x-nav-link>
    <x-nav-link :href="route('public-deck')" :active="request()->routeIs('public-deck')">Public decks</x-nav-link>
    <x-nav-link :href="$link('decks.builder')" :active="request()->routeIs('decks.builder')">Deck builder</x-nav-link>
</div>
@else
@auth
<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Home</x-nav-link>
@endauth

<div class="relative group inline-block text-left">
    <x-dropdown-button>Cards</x-dropdown-button>
    <x-dropdown-menu>
        <x-dropdown-nav-link :href="$link('cards.index')">Cards database</x-dropdown-nav-link>
        <x-dropdown-nav-link :href="$link('packs.index')">Packs</x-dropdown-nav-link>
    </x-dropdown-menu>
</div>

<div class="relative group inline-block text-left">
    <x-dropdown-button>Decks</x-dropdown-button>
    <x-dropdown-menu>
        <x-dropdown-nav-link :href="route('public-deck')">Public decks</x-dropdown-nav-link>
        <x-dropdown-nav-link :href="$link('decks.builder')">Deck builder</x-dropdown-nav-link>
    </x-dropdown-menu>
</div>
@endif
