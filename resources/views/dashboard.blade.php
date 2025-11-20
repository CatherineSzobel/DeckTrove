<x-layout>
    <h1 class="text-3xl font-bold underline text-center">
        Welcome to DeckTrove Dashboard
    </h1>

    <div class="flex justify-center items-center gap-6 mt-8">
        <x-scale-div>
            <img src="{{ Vite::asset('resources/img/yugioh.png') }}" 
                 alt="Yu-Gi-Oh! Logo" 
                 class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto cursor-pointer dashboard-logo"
                 data-series="yugioh">
        </x-scale-div>
        <x-scale-div>
            <img src="{{ Vite::asset('resources/img/magic.png') }}" 
                 alt="Magic: The Gathering Logo" 
                 class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto cursor-pointer dashboard-logo"
                 data-series="magic">
        </x-scale-div>
    </div>
    
</x-layout>
