@props(['links', 'label', 'as' => 'div', 'keyAs' => null, 'current' => 'page', 'bg' => 'bg-canvas', 'linkClass' => null])

{{--
    A segmented control made of plain links (works without JS). $links: [['label', 'href', 'current', 'key'?], ...].
    keyAs="view" adds data-view="<key>" to each link for scripts and tests; current="true" suits filters (not pages).
    data-label lets .segment reserve the bold width, so picking one doesn't shift the others.
--}}
<{{ $as }}@if ($as === 'div') role="group"@endif aria-label="{{ $label }}" {{ $attributes->class(['inline-flex flex-wrap gap-1 rounded-xl border border-line p-1', $bg]) }}>
    @foreach ($links as $link)
    <a href="{{ $link['href'] }}"@if ($keyAs) data-{{ $keyAs }}="{{ $link['key'] }}"@endif data-label="{{ $link['label'] }}"@if ($link['current']) aria-current="{{ $current }}"@endif class="segment{{ $linkClass ? " $linkClass" : '' }}">{{ $link['label'] }}</a>
    @endforeach
</{{ $as }}>
