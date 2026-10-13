@props(['label', 'value', 'accent' => false])

{{-- A big number with its label, e.g. the dashboard's deck counts. `accent` colours the number. --}}
<div {{ $attributes->class('flex items-baseline justify-between gap-3 rounded-2xl border border-line bg-surface px-6 py-5') }}>
    <span class="text-[15px] text-body">{{ $label }}</span>
    <span @class(['font-mono text-4xl font-bold leading-none', 'text-accent-ink' => $accent, 'text-ink' => ! $accent])>{{ $value }}</span>
</div>
