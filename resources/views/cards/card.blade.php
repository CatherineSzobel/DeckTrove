<x-layout>
    <x-label-button :class="'return_button'">Back to cards database</x-label-button>

    <div id="card_container" class="grid grid-cols-2 gap-4 py-8">
        @include("cards.$series.card.card-show")
    </div>
</x-layout>
