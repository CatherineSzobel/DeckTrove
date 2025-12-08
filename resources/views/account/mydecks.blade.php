<x-layout>
    <div class="text-center my-8">
        <h1 class="text-3xl font-bold">My decks</h1>
        <p class="text-gray-500">Manage your decks here</p>
        <div class="grid grid-cols-2 md:grid-cols-4 justify-center gap-6 px-4 ">
            @foreach ( $decks as $deck )
            <div class="col-span-1 md:col-span-2 flex flex-col justify-center items-center gap-4 mt-8 border rounded-md p-6 shadow-lg">
                <a href="/decks/{{ $deck->id }}" class="text-2xl font-bold">{{ $deck->name }}</a>
                <p class="text-gray-500"> {{ $deck->description }}</p>
                
            </div>
                @endforeach
        </div>
</x-layout>