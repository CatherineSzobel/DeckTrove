@props(['counts', 'card', 'series'])

{{-- A card tile's owned counter, from the page's App\Collection\TileCounts. --}}
<x-collection.counter :game="$series" :card="$card->id()" :name="$card->name()" :count="$counts->count($card)" :removable="$counts->removable($card)" {{ $attributes }} />
