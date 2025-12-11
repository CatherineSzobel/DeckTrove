<x-layout>
    <div class="max-w-3xl mx-auto mt-12 px-4">

        {{-- Profile Card --}}
        <div class="bg-white dark:bg-gray-900 shadow-xl rounded-2xl p-8 flex flex-col items-center">

            {{-- Avatar --}}
            <img
                class="w-28 h-28 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700"
                src="{{ auth()->user()->profile_photo_url ?? Vite::asset('resources/img/default-avatar.png') }}"
                alt="Profile Avatar">

            {{-- Username --}}
            <h1 class="font-extrabold text-3xl mt-4 text-gray-900 dark:text-white">
                {{ auth()->user()->name }}
            </h1>

            {{-- Email --}}
            <p class="text-gray-500 dark:text-gray-400 text-sm">
                {{ auth()->user()->email }}
            </p>

            {{-- Description --}}
            <p class="text-gray-700 dark:text-gray-300 mt-4 text-center max-w-lg leading-relaxed">
                {{ auth()->user()->about ?? 'No description available.' }}
            </p>

            {{-- Buttons --}}
            <div class="flex gap-4 mt-6">
                <a href="/decks"
                    class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold shadow-md hover:from-blue-600 hover:to-blue-700 transition-colors duration-300">
                    View Decks
                </a>

                <a href="/user/edit"
                    class="px-5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-xl font-medium shadow-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-300">
                    Edit Profile
                </a>
            </div>


        </div>

        {{-- Decks Section --}}
        <div class="mt-6 space-y-4">
            <h2 class="font-bold text-2xl mt-10 text-gray-900 dark:text-white">Your starred decks</h2>

            <p class="text-gray-500 dark:text-gray-400">You don't have any decks yet.</p>
        </div>
    </div>
</x-layout>