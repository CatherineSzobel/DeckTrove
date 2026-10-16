@props(['deck'])

{{-- A deck on one line (the profile's liked list): a small cover, the name (a link over the whole row) and "by {owner} · {series}". Expects `user` loaded. --}}
<li {{ $attributes->class('relative flex min-h-11 items-center gap-3.5 rounded-[14px] border border-line bg-surface p-3 transition hover:border-accent focus-within:border-accent') }}>
    @if ($deck->image)
    <img src="{{ $deck->image }}" alt="" loading="lazy" class="aspect-[59/86] w-10 shrink-0 rounded object-cover">
    @else
    <span class="aspect-[59/86] w-10 shrink-0 rounded border border-line bg-surface-2" aria-hidden="true"></span>
    @endif
    <div class="flex min-w-0 flex-col gap-0.5">
        <a href="{{ route('decks.show', $deck) }}" class="focus-ring truncate rounded-sm font-bold text-ink after:absolute after:inset-0 after:rounded-[14px] after:content-['']">{{ $deck->name }}</a>
        <span class="truncate text-[13px] text-muted">by {{ $deck->user->username }} · {{ $deck->series_label }}</span>
    </div>
</li>
