@props(['deck', 'variant' => 'public', 'trending' => false, 'heading' => 'h2'])

{{--
    A deck in a grid: the deck page's cover, the name (a link stretched over the whole card), format, description and
    likes. Variants: `public` adds the author; `profile` leaves the author out (it's their page); `owner` (My decks)
    adds the visibility and the Edit / Delete actions. `trending` shows the "+N this week" chip.
    Expects card_count and likes_count loaded; liked_by_viewer and recent_likes_count when available.
--}}
@php
    $seriesLabel = config("series.{$deck->game}.label", ucfirst($deck->game));
    $likes = (int) ($deck->likes_count ?? 0);
    $recent = (int) ($deck->recent_likes_count ?? 0);
    $formatTitle = $deck->format ? "Built for {$deck->format_label}." : 'No format: only the normal deck rules apply.';
    $author = $deck->user;
@endphp

<article {{ $attributes->class('relative flex flex-col overflow-hidden rounded-[18px] border border-line bg-surface transition hover:-translate-y-0.5 hover:border-accent focus-within:border-accent') }}>
    <div class="relative flex h-[190px] items-center justify-center border-b border-line bg-canvas">
        <div class="relative h-40 w-44" aria-hidden="true">
            <div class="absolute left-[18px] top-7 aspect-[59/86] w-[92px] -rotate-12 rounded-lg bg-surface-2 shadow-lg"></div>
            <div class="absolute left-[66px] top-7 aspect-[59/86] w-[92px] rotate-[10deg] rounded-lg bg-surface-2 shadow-lg"></div>
            @if ($deck->image)
            <img src="{{ $deck->image }}" alt="" loading="lazy" class="absolute left-[42px] top-2.5 aspect-[59/86] w-[92px] rounded-lg object-cover shadow-xl">
            @else
            <div class="absolute left-[42px] top-2.5 aspect-[59/86] w-[92px] rounded-lg border border-line-strong bg-canvas shadow-xl"></div>
            @endif
        </div>

        @if ($trending && $recent > 0)
        <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-[10px] border border-warn-line bg-surface px-2.5 py-1.5 font-mono text-xs font-bold text-accent-ink">
            <x-heroicon-o-arrow-trending-up class="h-4 w-4" aria-hidden="true" />
            +{{ $recent }} this week
        </span>
        @endif
        <span class="absolute right-3 top-3 inline-flex items-baseline gap-1.5 rounded-[10px] border border-line bg-surface px-2.5 py-1.5 text-xs text-body">
            <b class="font-mono text-sm text-ink">{{ (int) $deck->card_count }}</b>cards
        </span>
    </div>

    <div class="flex flex-1 flex-col gap-3 p-5">
        <p class="eyebrow">{{ $seriesLabel }} deck</p>
        <{{ $heading }} class="heading-display truncate text-[28px] text-ink">
            <a href="{{ route('decks.show', $deck) }}" class="focus-ring rounded-sm after:absolute after:inset-0 after:rounded-[18px] after:content-['']">{{ $deck->name }}</a>
        </{{ $heading }}>

        <div class="flex flex-wrap gap-2">
            <span title="{{ $formatTitle }}" class="inline-flex h-[30px] items-center rounded-full border border-line bg-surface-2 px-3 font-mono text-xs font-bold uppercase tracking-[0.04em] text-body">{{ $deck->format_label }}</span>
            @if ($variant === 'owner')
            <span @class([
                'inline-flex h-[30px] items-center gap-1.5 rounded-full border px-3 font-mono text-xs font-bold uppercase tracking-[0.04em]',
                'border-ok-line bg-ok-bg text-ok' => $deck->is_public,
                'border-line text-muted' => ! $deck->is_public,
            ])>
                @if ($deck->is_public)
                <x-heroicon-o-globe-alt class="h-4 w-4" aria-hidden="true" />Public
                @else
                <x-heroicon-o-lock-closed class="h-4 w-4" aria-hidden="true" />Private
                @endif
            </span>
            @endif
        </div>

        <p class="line-clamp-3 text-[15px] leading-relaxed text-body">{{ $deck->description ?: 'No description provided.' }}</p>

        <div class="mt-auto flex items-center justify-between gap-3 border-t border-line pt-3 text-sm">
            @if ($variant === 'public' && $author)
            <a href="{{ route('users.show', $author) }}" class="focus-ring relative z-10 inline-flex min-h-11 min-w-0 items-center gap-2 rounded-sm text-body hover:text-ink hover:underline">
                @if ($author->avatarUrl())
                <img src="{{ $author->avatarUrl() }}" alt="" class="h-7 w-7 flex-none rounded-full object-cover">
                @else
                <span aria-hidden="true" class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-accent text-xs font-bold text-white">{{ Str::upper(Str::substr($author->username, 0, 1)) }}</span>
                @endif
                <span class="truncate"><span class="sr-only">by </span>{{ $author->username }}</span>
            </a>
            @elseif ($variant === 'profile')
            <span aria-hidden="true" class="inline-flex items-center gap-1.5 font-bold text-accent-ink">
                View deck
                <x-heroicon-o-arrow-right class="h-[18px] w-[18px]" />
            </span>
            @elseif ($variant === 'owner')
            <div class="relative z-10 flex items-center gap-2">
                <a href="{{ route('decks.edit', $deck) }}" class="focus-ring inline-flex h-11 items-center gap-1.5 rounded-xl border-[1.5px] border-line-strong px-3 font-bold text-ink transition hover:bg-surface-2">
                    <x-heroicon-o-pencil-square class="h-[18px] w-[18px]" aria-hidden="true" />
                    Edit
                </a>
                <form method="POST" action="{{ route('decks.destroy', $deck) }}"
                    data-confirm="“{{ $deck->name }}” will be deleted. This cannot be undone." data-confirm-title="Delete this deck?" data-confirm-button="Delete deck">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="Delete {{ $deck->name }}"
                        class="focus-ring inline-flex h-11 w-11 items-center justify-center rounded-xl border-[1.5px] border-line-strong text-body transition hover:border-danger-line hover:bg-danger-bg hover:text-danger">
                        <x-heroicon-o-trash class="h-[18px] w-[18px]" aria-hidden="true" />
                    </button>
                </form>
            </div>
            @endif

            @if ($variant !== 'owner' || $deck->is_public)
            <x-deck.like-button :deck="$deck" :liked="(bool) ($deck->liked_by_viewer ?? false)" :count="$likes" />
            @endif
        </div>
    </div>
</article>
