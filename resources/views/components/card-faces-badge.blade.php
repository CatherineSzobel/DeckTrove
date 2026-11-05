{{-- "2 faces" on a double-faced card's tile (App\View\Components\CardFacesBadge). --}}
<span class="inline-flex items-center gap-1 rounded-full border border-line bg-surface px-2 py-0.5 text-[11px] font-bold text-ink {{ $classes() }}">
    <x-heroicon-o-arrow-path class="h-3.5 w-3.5" aria-hidden="true" />
    <span aria-hidden="true">{{ $label() }}</span>
    <span class="sr-only">Double-faced card</span>
</span>
