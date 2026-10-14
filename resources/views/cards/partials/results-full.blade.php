{{-- Full view: one card per box with its text, set, rarity and price. The name's link covers the whole box. --}}
<div data-results class="grid grid-cols-[repeat(auto-fill,minmax(min(340px,100%),1fr))] gap-4">
    @foreach ($cards as $card)
    <article class="relative flex gap-4 rounded-2xl border border-line bg-surface p-4 transition hover:border-accent focus-within:border-accent">
        <img src="{{ $card->imageSmall() }}" alt="" loading="lazy" class="aspect-[59/86] w-[120px] shrink-0 self-start rounded-md object-cover shadow-md">
        <div class="flex min-w-0 flex-1 flex-col gap-2">
            <h2 class="heading-display text-[22px] text-ink">
                <a href="{{ $card->link() }}" class="focus-ring rounded-sm after:absolute after:inset-0 after:content-['']">{{ $card->name() }}</a>
            </h2>
            <p class="text-[13px] text-muted">{{ $card->typeLine() }}</p>
            @if ($card->hasStats())
            <p class="font-mono text-[13px] text-body">{{ $card->statLine() }}</p>
            @endif
            @if ($card->description())
            <p class="line-clamp-5 whitespace-pre-line text-sm text-body">{{ $card->description() }}</p>
            @endif
            <div class="mt-auto flex flex-wrap items-center gap-2 pt-2 text-xs text-muted">
                <span class="min-w-0 truncate">{{ $card->setName() }}</span>
                <span class="rarity-pill">{{ $card->rarityLabel() }}</span>
                <span class="ml-auto font-mono text-sm font-bold text-ink">{{ $card->formattedPrice() }}</span>
            </div>
        </div>
    </article>
    @endforeach
</div>
