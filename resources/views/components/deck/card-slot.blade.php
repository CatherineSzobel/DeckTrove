@props(['card', 'over' => null, 'href' => null])

{{--
    One card in a deck: its image in a card-shaped slot. `over` is the message for a card that breaks the deck's
    format (red outline and "!"). With `href` the slot is a link. The slot's content (e.g. a preview) goes inside.
--}}
@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif @if ($over) title="{{ $over }}" @endif
    {{ $attributes->class([
        'group relative block aspect-[59/86] rounded-md bg-surface-2 shadow-md transition-transform hover:-translate-y-0.5',
        'focus-ring' => $href,
        'outline-2 outline-offset-2 outline-danger' => $over,
    ]) }}>
    <img src="{{ $card->imageSmall() }}" alt="{{ $card->name() }}" loading="lazy" class="h-full w-full rounded-md object-cover">
    @if ($over)
    <span aria-hidden="true" class="absolute -right-2 -top-2 flex h-[22px] w-[22px] items-center justify-center rounded-full bg-danger font-mono text-[13px] font-bold text-canvas">!</span>
    <span class="sr-only">{{ $over }}</span>
    @endif
    {{ $slot }}
</{{ $tag }}>
