<x-layout :css="['resources/css/home.css']">
    <h1 class="text-3xl font-bold underline text-center">
        Welcome to DeckTrove Dashboard
    </h1>

    <div class="flex justify-center items-center gap-6 mt-8 container">
        <img src="{{ Vite::asset('resources/img/yugioh.png') }}"
            alt="Yu-Gi-Oh! Logo"
            class=" cursor-pointer dashboard-logo"
            data-series="yugioh">
        <img src="{{ Vite::asset('resources/img/magic.png') }}"
            alt="Magic: The Gathering Logo"
            class="cursor-pointer dashboard-logo"
            data-series="magic">
    </div>

</x-layout>