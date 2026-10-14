{{-- The pack list: a GET search, Full / List view links and numbered pages, so it works without JS. --}}

<x-layout :title="$seriesLabel.' packs'" :game-switch="false">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-col gap-2">
                <p class="eyebrow">Sets &amp; releases</p>
                <h1 class="heading-display text-[clamp(40px,5vw,60px)] text-ink">{{ $seriesLabel }} packs</h1>
            </div>
            <x-game-switch :series="$series" />
        </div>

        <section aria-label="Search packs" class="flex flex-wrap items-center gap-3 rounded-[18px] border border-line bg-surface p-4">
            <form method="GET" action="{{ route('packs.index', $series) }}" role="search" class="flex min-w-0 flex-[1_1_360px] gap-2">
                @foreach ($hiddenFields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <div class="relative min-w-0 flex-1">
                    <label for="pack-search" class="sr-only">Search packs</label>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-muted" aria-hidden="true" />
                    <input id="pack-search" type="search" name="search" value="{{ $search }}" maxlength="100"
                        placeholder="Search packs by name or code" class="input w-full pl-11">
                </div>
                <button type="submit" class="btn-primary">Search</button>
            </form>

            <nav aria-label="Pack view" class="inline-flex gap-1 rounded-xl border border-line bg-canvas p-1">
                @foreach ($viewLinks as $link)
                <a href="{{ $link['href'] }}" data-view="{{ $link['key'] }}" @if ($link['current']) aria-current="page" @endif class="segment">{{ $link['label'] }}</a>
                @endforeach
            </nav>
        </section>

        @if ($resultLine)
        <div class="flex flex-wrap items-center gap-3 text-sm">
            <p aria-live="polite" class="text-muted">{{ $resultLine }}</p>
            <a href="{{ $clearUrl }}" class="focus-ring rounded-sm font-bold text-accent-ink">Clear search</a>
        </div>
        @endif

        @if ($packs->isEmpty())
        <x-empty-state title="No packs found">
            Try another name or code.
            <x-slot:actions>@if ($resultLine)<a href="{{ $clearUrl }}" class="btn-secondary">Clear search</a>@endif</x-slot:actions>
        </x-empty-state>
        @else
        @include("packs.partials.view-{$currentView}")
        @endif

        <x-pagination :paginator="$packs" />
    </div>
</x-layout>
