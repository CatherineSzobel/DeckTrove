<x-layout :title="$card->name()" :js="['resources/js/card.js']">
    <div class="grid grid-cols-1 gap-10 py-8">
        @include('cards.partials.show')
    </div>
</x-layout>
