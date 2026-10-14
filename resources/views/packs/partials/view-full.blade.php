{{-- Full view: one tile per pack with its cover, code, release date and card count. --}}
<div class="grid grid-cols-[repeat(auto-fill,minmax(min(340px,100%),1fr))] gap-4">
    @foreach ($packs as $pack)
    <a href="{{ $pack->link() }}" class="pack-tile flex items-center gap-4 p-4">
        <x-pack.cover :pack="$pack" size="sm" />
        <span class="flex min-w-0 flex-1 flex-col gap-1.5">
            <span class="text-[17px] font-bold leading-tight text-ink">{{ $pack->name() }}</span>
            <span class="font-mono text-xs text-muted">{{ $pack->metaLine() }}</span>
        </span>
        @if ($pack->cardCount())
        <span class="flex shrink-0 flex-col items-end gap-0.5">
            <span class="font-mono text-[22px] font-bold text-accent-ink">{{ $pack->cardCount() }}</span>
            <span class="text-xs text-muted">cards</span>
        </span>
        @endif
    </a>
    @endforeach
</div>
