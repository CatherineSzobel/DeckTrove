@props(['label', 'value', 'size' => 'md'])

{{--
    A label above a big value, e.g. ATK 2500 on the card page or Released on a pack page. Use it inside a <dl>
    (it renders dt/dd). `sm` has a smaller value.
--}}
<div {{ $attributes->class('flex min-w-[110px] flex-col gap-1 rounded-[14px] border border-line bg-surface px-[18px] py-3.5') }}>
    <dt class="font-mono text-[11px] uppercase tracking-[0.08em] text-muted">{{ $label }}</dt>
    <dd @class(['m-0 font-mono font-bold text-ink', 'text-[26px]' => $size === 'md', 'text-lg' => $size === 'sm'])>{{ $value }}</dd>
</div>
