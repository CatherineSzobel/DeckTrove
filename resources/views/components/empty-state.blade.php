@props(['title', 'heading' => 'h2'])

{{-- The dashed "nothing here" box: a title, an optional line of text (the slot), an optional icon and actions. --}}
<section {{ $attributes->class('flex flex-col items-center gap-3 rounded-2xl border-2 border-dashed border-line-strong p-10 text-center') }}>
    {{ $icon ?? '' }}
    <{{ $heading }} class="heading-display text-[30px] text-ink">{{ $title }}</{{ $heading }}>
    @if ($slot->isNotEmpty())
    <p class="max-w-[32rem] text-[15px] text-muted">{{ $slot }}</p>
    @endif
    @if (isset($actions) && $actions->isNotEmpty())
    <div class="flex flex-wrap justify-center gap-3">{{ $actions }}</div>
    @endif
</section>
