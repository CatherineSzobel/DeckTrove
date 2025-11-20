<x-layout>
    <div class="text-center my-8">
        <h1 class="text-3xl font-bold underline">
            Public decks
        </h1>
    </div>

    <div class="flex flex-wrap justify-center gap-6 px-4">
        <div class="flex flex-col justify-center items-center gap-4 mt-8 border rounded-md p-6 shadow-lg">
            <img src={{ Vite::asset('resources/img/yugioh.png') }} alt="Yu-Gi-Oh! Logo" class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto">
            <h1 class="font-bold text-lg">Public deck 1</h1>
            <p class="text-gray-500 text-center">Deck description</p>
            <p class="text-gray-700">Deck author</p>
        </div>

        <div class="flex flex-col justify-center items-center gap-4 mt-8 border rounded-md p-6 shadow-lg">
            <img src={{ Vite::asset('resources/img/yugioh.png') }} alt="Yu-Gi-Oh! Logo" class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto">
            <h1 class="font-bold text-lg">Public deck 2</h1>
            <p class="text-gray-500 text-center">Deck description</p>
            <p class="text-gray-700">Deck author</p>
        </div>

        <div class="flex flex-col justify-center items-center gap-4 mt-8 border rounded-md p-6 shadow-lg">
            <img src={{ Vite::asset('resources/img/yugioh.png') }} alt="Yu-Gi-Oh! Logo" class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto">
            <h1 class="font-bold text-lg">Public deck 3</h1>
            <p class="text-gray-500 text-center">Deck description</p>
            <p class="text-gray-700">Deck author</p>
        </div>
    </div>
</x-layout>
