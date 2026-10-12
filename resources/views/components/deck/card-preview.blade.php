{{--
    The card preview panel shared by the deck builder and the deck page: one fixed panel placed next to the hovered or
    focused card (state and placement: cardPreview() in resources/js/deckbuilder/preview.js). Must sit inside the
    Alpine component that has that state; `preview` is a CardViewModel::builderCard() array.
--}}
<div x-ref="preview" x-show="preview" x-cloak data-card-preview aria-hidden="true"
    class="pointer-events-none fixed z-50 flex w-72 gap-3 rounded-[14px] border border-line-strong bg-canvas p-4 text-left shadow-2xl"
    :style="`left: ${previewPos.left}px; top: ${previewPos.top}px`">
    <img :src="preview?.imageSmall" alt="" class="w-20 flex-none self-start rounded-md">
    <div class="flex min-w-0 flex-col gap-1.5">
        <span class="heading-display text-xl text-ink" x-text="preview?.name"></span>
        <span class="font-mono text-[11px] text-muted" x-text="previewLine()"></span>
        <span class="line-clamp-6 text-[13px] leading-snug text-body" x-text="preview?.description || 'No description'"></span>
    </div>
</div>
