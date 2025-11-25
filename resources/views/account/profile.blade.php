<x-layout>
    <div class="max-w-3xl mx-auto mt-10">

        {{-- Profile Card --}}
        <div class="bg-white shadow-md rounded-xl p-6 flex flex-col items-center">

            {{-- Avatar --}}
            <img
                class="w-24 h-24 rounded-full object-cover border"
                src="{{ auth()->user()->profile_photo_url ?? Vite::asset('resources/img/default-avatar.png') }}"
                alt="Profile Avatar">

            {{-- Username --}}
            <h1 class="font-bold text-2xl mt-4">
                {{ auth()->user()->name }}
            </h1>

            {{-- Email --}}
            <p class="text-gray-500 text-sm">
                {{ auth()->user()->email }}
            </p>

            {{-- Description --}}
            <p class="text-gray-700 mt-4 text-center max-w-md">
                {{ auth()->user()->about ?? 'No description available.' }}
            </p>

            {{-- Buttons --}}
            <div class="flex gap-3 mt-6">
                <a href="/decks"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    View Decks
                </a>

                <a href="/user/edit"
                    class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                    Edit Profile
                </a>
            </div>

            <h2 class="font-bold text-xl mt-8">Your Decks</h2>



        </div>
        <div class="mt-4 space-y-3">
            <p class="text-gray-500">You don't have any decks yet.</p>
        </div>
    </div>

</x-layout>