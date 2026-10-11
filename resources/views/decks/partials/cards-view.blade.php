{{--
    Search results in the deck builder; also returned on its own for infinite scroll. Each result carries its card
    (CardViewModel::builderCard()) for the builder component's add / remove / cover actions.
--}}
@foreach ($cards as $card)
<div data-card-id="{{ $card->id() }}" x-data="{ card: {{ Js::from($card->builderCard()) }} }" class="group/result flex min-w-0 flex-col gap-1.5">
    <button type="button" @click="setCover(card)" title="Set as deck cover" aria-label="Use {{ $card->name() }} as the deck cover"
        class="focus-ring relative block aspect-[59/86] rounded-md bg-surface-2 shadow-md">
        <img src="{{ $card->imageSmall() }}" alt="{{ $card->name() }}" loading="lazy" class="h-full w-full rounded-md object-cover">

        <span class="dark-scope pointer-events-none absolute inset-0 hidden flex-col justify-center gap-1 rounded-md bg-canvas/85 p-2 text-left group-hover/result:flex group-focus-within/result:flex">
            <span class="text-xs font-bold leading-tight text-ink">{{ $card->name() }}</span>
            <span class="line-clamp-6 text-[10px] leading-snug text-body">{{ $card->description() }}</span>
        </span>

        <span x-show="copiesOf(card) > 0" x-cloak x-text="copiesLabel(card)"
            class="dark-scope absolute right-1.5 top-1.5 rounded-full bg-canvas px-1.5 py-0.5 font-mono text-[11px] font-bold"
            :class="isOverLimit(card) ? 'text-danger' : 'text-ink'"></span>
        <span x-show="limitTag(card)" x-cloak x-text="limitTag(card)"
            class="dark-scope absolute inset-x-1.5 bottom-1.5 truncate rounded bg-canvas/90 px-1 py-1 text-center font-mono text-[10px] font-bold uppercase tracking-[0.04em]"
            :class="{ 'text-danger': limitOf(card) === 0, 'text-amber-300': limitOf(card) === 1, 'text-sky-300': limitOf(card) >= 2 }"></span>
    </button>

    <span class="truncate text-center text-[11px] text-muted" title="{{ $card->fullType() }}">{{ $card->name() }}</span>

    <div class="flex gap-1.5">
        <button type="button" @click="removeOne(card)" :disabled="!canRemove(card)" aria-label="Remove {{ $card->name() }} from the active zone"
            class="focus-ring flex h-11 flex-1 items-center justify-center rounded-[10px] border-[1.5px] border-line-strong text-ink hover:bg-surface-2 disabled:cursor-not-allowed disabled:opacity-35">
            <x-heroicon-o-minus class="h-5 w-5" aria-hidden="true" />
        </button>
        <button type="button" @click="add(card)" aria-label="Add {{ $card->name() }} to the active zone"
            class="focus-ring flex h-11 flex-1 items-center justify-center rounded-[10px] bg-accent text-white hover:brightness-110">
            <x-heroicon-o-plus class="h-5 w-5" aria-hidden="true" />
        </button>
    </div>
</div>
@endforeach

@if ($cards->isEmpty() && $cards->currentPage() === 1)
<p class="col-span-full py-8 text-center text-sm text-muted">No cards found. Try another search or clear the filters.</p>
@endif
