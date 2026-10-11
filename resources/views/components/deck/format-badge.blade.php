@props(['format' => null, 'label' => null, 'problems' => 0])

{{-- A deck's format: neutral "Casual", green when the deck fits its format, red when it breaks it. --}}
@php
    $state = ! $format ? 'casual' : ($problems > 0 ? 'broken' : 'legal');
    $label ??= $format;
    [$text, $title, $classes] = match ($state) {
        'casual' => ['Casual', 'No format: only the normal deck rules apply.', 'border-line bg-surface-2 text-body'],
        'legal' => [$label, "Legal in $label.", 'border-ok-line bg-ok-bg text-ok'],
        'broken' => [$label, "This deck no longer fits $label.", 'border-danger-line bg-danger-bg text-danger'],
    };
@endphp

<span title="{{ $title }}" data-format-badge="{{ $state }}"
    {{ $attributes->class(['inline-flex h-9 items-center gap-2 rounded-full border px-3.5 font-mono text-[13px] font-bold uppercase tracking-[0.04em]', $classes]) }}>
    @if ($state === 'legal')
    <x-heroicon-o-check class="h-4 w-4" aria-hidden="true" />
    @elseif ($state === 'broken')
    <x-heroicon-o-exclamation-triangle class="h-4 w-4" aria-hidden="true" />
    @endif
    {{ $text }}
    <span class="sr-only">{{ $title }}</span>
</span>
