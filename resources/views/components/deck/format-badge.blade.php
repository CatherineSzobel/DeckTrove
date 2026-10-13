{{-- A deck's format; App\View\Components\Deck\FormatBadge picks the state, text and tooltip, app.css the colours. --}}
<span title="{{ $title }}" data-format-badge="{{ $state }}" {{ $attributes->class('format-badge') }}>
    @if ($state === 'legal')
    <x-heroicon-o-check class="h-4 w-4" aria-hidden="true" />
    @elseif ($state === 'broken')
    <x-heroicon-o-exclamation-triangle class="h-4 w-4" aria-hidden="true" />
    @endif
    {{ $text }}
    <span class="sr-only">{{ $title }}</span>
</span>
