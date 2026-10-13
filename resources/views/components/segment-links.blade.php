@props(['links', 'label'])

{{-- A segmented control made of plain links (works without JS). $links: [['label', 'href', 'current'], ...]. --}}
<div role="group" aria-label="{{ $label }}" {{ $attributes->class('inline-flex flex-wrap gap-1 rounded-xl border border-line bg-canvas p-1') }}>
    @foreach ($links as $link)
    <a href="{{ $link['href'] }}" @if ($link['current']) aria-current="page" @endif class="segment">{{ $link['label'] }}</a>
    @endforeach
</div>
