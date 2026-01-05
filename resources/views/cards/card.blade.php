<x-layout :js="['resources/js/card.js']">
    <div id="card_container" class="grid grid-cols-1 gap-10 py-8">
        @include("cards.partials.show")
    </div>
</x-layout>
