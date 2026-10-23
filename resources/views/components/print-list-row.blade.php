@props(['pack', 'counters' => null])

{{-- One printing in x-print-list: the set name and its code, linked to the pack page; for a player, its − n +. --}}
<li class="flex items-center gap-3 border-b border-line">
    @if ($pack['url'])
    <a href="{{ $pack['url'] }}" class="focus-ring flex min-h-12 min-w-0 flex-1 items-center justify-between gap-3 font-medium text-ink hover:text-accent-ink">
        <span>{{ $pack['set_name'] }}</span>
        <span class="font-mono text-[13px] text-accent-ink">{{ $pack['code'] }}</span>
    </a>
    @else
    <span class="flex min-h-12 min-w-0 flex-1 items-center justify-between gap-3 font-medium text-ink">
        <span>{{ $pack['set_name'] }}</span>
        <span class="font-mono text-[13px] text-accent-ink">{{ $pack['code'] }}</span>
    </span>
    @endif
    @if ($counters)
    <x-collection.counter :game="$counters['game']" :card="$counters['card']" :name="$pack['name']" :printing="$pack['printing']" :count="$pack['count']" anchor="printings" />
    @endif
</li>
