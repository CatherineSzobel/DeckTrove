{{-- The inside of a game tile on the home page; the caller wraps it in a link (live) or a div (coming soon). --}}
<span class="game-tile-inner">
    <span class="flex items-center justify-between gap-2">
        <span class="font-mono text-[13px]">{{ $game['number'] }}</span>
        @if ($game['live'])
        <span class="rounded-full bg-accent px-2 py-0.5 font-mono text-[11px] font-bold uppercase tracking-[0.06em] text-white">Live</span>
        @else
        <span class="rounded-full bg-[#1A1715]/10 px-2 py-0.5 font-mono text-[11px] font-bold uppercase tracking-[0.06em]">Coming soon</span>
        @endif
    </span>
    <span class="font-display text-[36px] font-bold leading-none">{{ $game['label'] }}</span>
    <span class="flex flex-col gap-2">
        <span class="text-sm">{{ $game['tagline'] }}</span>
        @if ($game['live'])
        <span class="inline-flex items-center gap-1.5 text-sm font-bold text-[#B23A12]">
            Browse cards
            <x-heroicon-o-arrow-right class="h-4 w-4" aria-hidden="true" />
        </span>
        @endif
    </span>
</span>
