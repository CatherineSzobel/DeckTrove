{{-- The quiet − n + (App\View\Components\Collection\Counter): two plain POST forms (CollectionItemController) around
    the count. With JS, collectionCounter (resources/js/collection/counter.js) posts in place, updates the count and
    follows the page's other counters for the same card. --}}
<div x-data="collectionCounter" data-count="{{ $count }}" data-removable="{{ $removableFlag() }}" data-game="{{ $game }}"
    data-card="{{ $card }}" data-list="{{ $list }}" data-printing="{{ $printing }}" x-init="start($el)" @collection-changed.window="sync($event.detail)"
    {{ $attributes->class('flex flex-col items-center') }}>
    <div class="flex items-center justify-center gap-1">
        <form method="POST" action="{{ route('collection.remove') }}" @submit="submit($event)">
            @csrf
            <input type="hidden" name="game" value="{{ $game }}">
            <input type="hidden" name="card" value="{{ $card }}">
            <input type="hidden" name="list" value="{{ $list }}">
            <input type="hidden" name="printing" value="{{ $printing }}">
            @if ($anchor)
            <input type="hidden" name="anchor" value="{{ $anchor }}">
            @endif
            <button type="submit" @disabled(! $canRemove()) :disabled="count < 1 || ! removable" aria-label="{{ $removeLabel() }}"
                @if ($hint()) aria-describedby="{{ $hintId() }}" title="{{ $hint() }}" @endif class="counter-button">
                <x-heroicon-o-minus class="h-4 w-4" aria-hidden="true" />
            </button>
        </form>
        <span aria-live="polite" x-text="count" :data-zero="count < 1" class="counter-count {{ $countClass() }}" @if ($isZero()) data-zero @endif>{{ $count }}</span>
        <form method="POST" action="{{ route('collection.add') }}" @submit="submit($event)">
            @csrf
            <input type="hidden" name="game" value="{{ $game }}">
            <input type="hidden" name="card" value="{{ $card }}">
            <input type="hidden" name="list" value="{{ $list }}">
            <input type="hidden" name="printing" value="{{ $printing }}">
            @if ($anchor)
            <input type="hidden" name="anchor" value="{{ $anchor }}">
            @endif
            <button type="submit" aria-label="{{ $addLabel() }}" class="counter-button">
                <x-heroicon-o-plus class="h-4 w-4" aria-hidden="true" />
            </button>
        </form>
    </div>
    @if ($hint())
    <span id="{{ $hintId() }}" class="sr-only">{{ $hint() }}</span>
    @endif
    <span x-show="error" x-text="error" x-cloak role="alert" class="text-center text-xs text-danger"></span>
</div>
