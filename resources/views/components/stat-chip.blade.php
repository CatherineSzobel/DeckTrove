@props(['value', 'label', 'accent' => false, 'warn' => false, 'inset' => false, 'size' => 'md'])

{{--
    A number with its label, e.g. "12 decks". `accent` colours the number, `warn` the border (e.g. the deck price),
    `inset` is for chips sitting on a card (canvas background instead of surface), `sm` the small chip on deck covers.
--}}
<span {{ $attributes->class([
    'inline-flex items-baseline rounded-[10px] border text-body',
    'gap-2 px-3 py-2 text-[13px]' => $size !== 'sm',
    'gap-1.5 px-2.5 py-1.5 text-xs' => $size === 'sm',
    'border-warn-line' => $warn,
    'border-line' => ! $warn,
    'bg-canvas' => $inset,
    'bg-surface' => ! $inset,
]) }}>
    <b @class(['font-mono', 'text-sm' => $size === 'sm', 'text-base' => $size !== 'sm', 'text-accent-ink' => $accent, 'text-ink' => ! $accent])>{{ $value }}</b>
    {{ $label }}
</span>
