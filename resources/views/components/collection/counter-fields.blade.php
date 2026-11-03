@props(['game', 'card', 'list', 'printing', 'anchor' => null])

{{-- What both of a counter's forms post (CollectionCardRequest). --}}
@csrf
<input type="hidden" name="game" value="{{ $game }}">
<input type="hidden" name="card" value="{{ $card }}">
<input type="hidden" name="list" value="{{ $list }}">
<input type="hidden" name="printing" value="{{ $printing }}">
@if ($anchor)
<input type="hidden" name="anchor" value="{{ $anchor }}">
@endif
