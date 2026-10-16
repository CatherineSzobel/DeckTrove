{{-- An overview section's heading row: the h2, the "Only you" badge when asked, and the "See all" link when there is more. --}}
<div class="flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
        <h2 id="{{ $id }}-title" class="heading-display text-[40px] text-ink">{{ $title }}</h2>
        @if ($private ?? false)
        @include('users.partials.private-badge')
        @endif
    </div>
    @if ($more)
    <a href="{{ $more['href'] }}" class="focus-ring flex min-h-11 items-center gap-1.5 rounded-sm font-bold text-accent-ink">
        {{ $more['label'] }}
        <x-heroicon-o-arrow-right class="h-4 w-4" aria-hidden="true" />
    </a>
    @endif
</div>
