<x-layout>
    <div class="text-center my-8">
        <h1 class="text-3xl font-bold underline">{{ $deck->name }}</h1>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 justify-center gap-6 px-4">
        @foreach ($cards as $card)
        <div class="flex flex-col justify-center items-center gap-4 mt-8 border rounded-md p-6 shadow-lg">

            <!-- Use REAL card image -->
            <img src="{{ $card['image_uris'] ?? 'https://via.placeholder.com/200x280?text=No+Image' }}"
                alt="{{ $card['name'] }}"
                class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto">

            <h2 class="text-2xl font-bold">{{ $card['name'] }}</h2>

        </div>
        @endforeach
    </div>
</x-layout>