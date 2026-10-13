@props(['deck', 'size' => 'md'])

{{-- A deck's cover in front of two tilted card shapes, or an empty card when it has no cover. Sizes: the .cover-fan class in app.css. --}}
<div class="cover-fan" data-size="{{ $size }}" aria-hidden="true">
    <div class="cover-fan-card cover-fan-left"></div>
    <div class="cover-fan-card cover-fan-right"></div>
    @if ($deck->image)
    <img src="{{ $deck->image }}" alt="" loading="lazy" class="cover-fan-card cover-fan-front">
    @else
    <div class="cover-fan-card cover-fan-front cover-fan-empty"></div>
    @endif
</div>
