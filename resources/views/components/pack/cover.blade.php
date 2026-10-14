{{-- A pack's cover (App\View\Components\Pack\Cover). Decorative: the pack's name is always next to it. --}}
<div class="pack-cover" data-size="{{ $size }}" data-kind="{{ $kind }}" data-tone="{{ $pack->tone() }}" aria-hidden="true">
    @if ($kind === 'art')
    <img src="{{ $pack->image() }}" alt="" loading="lazy">
    @else
    @if ($kind === 'symbol')
    <span class="pack-cover-symbol"><img src="{{ $pack->image() }}" alt="" loading="lazy"></span>
    @endif
    <span class="pack-cover-code">{{ $pack->codeLabel() }}</span>
    @endif
</div>
