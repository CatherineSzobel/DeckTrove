<x-layout>

    <div class="grid grid-cols-5 grid-rows-5 gap-4 border">
        <div class="col-span-3 border">
           <img src="{{ Vite::asset('resources/img/decktrove-logo.png') }}" alt="Decktrove Logo" class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto">
        </div>
        <div class="col-span-3 row-span-4 col-start-1 row-start-2 border">
            <div class="grid grid-cols-2 grid-rows-2 gap-4">
               <h1>Your decklists</h1>
            </div>
        </div>
        <div class="row-span-5 col-start-5 row-start-1 border">5</div>
    </div>

</x-layout>