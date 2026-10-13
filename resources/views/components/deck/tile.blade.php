@props(['deck', 'heading' => 'h3'])

{{--
    A compact deck for the dashboard: cover fan, card count, the name (a link stretched over the whole tile), series and
    visibility. Expects card_count loaded.
--}}
<article {{ $attributes->class('relative flex flex-col overflow-hidden rounded-2xl border-[1.5px] border-line bg-canvas transition hover:-translate-y-1 hover:border-accent focus-within:border-accent') }}>
    <div class="relative flex h-[150px] items-center justify-center border-b border-line bg-surface">
        <x-deck.cover-fan :deck="$deck" size="sm" />
        <x-stat-chip :value="(int) $deck->card_count" label="cards" size="sm" class="absolute right-2.5 top-2.5" />
    </div>

    <div class="flex flex-col gap-1.5 px-4 pb-4 pt-3.5">
        <{{ $heading }} class="heading-display truncate text-[22px] text-ink">
            <a href="{{ route('decks.show', $deck) }}" class="focus-ring rounded-sm after:absolute after:inset-0 after:rounded-2xl after:content-['']">{{ $deck->name }}</a>
        </{{ $heading }}>
        <p class="flex justify-between gap-3 text-[13px] text-muted">
            <span>{{ $deck->series_label }}</span>
            <span>{{ $deck->visibility_label }}</span>
        </p>
    </div>
</article>
