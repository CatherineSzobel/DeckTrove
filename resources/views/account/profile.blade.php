<x-layout>
    <div class="max-w-sm mx-auto mt-12">

        {{-- Card --}}
        <div class="bg-white dark:bg-gray-900 shadow-xl rounded-2xl flex flex-col items-center">

            {{-- Top Image --}}
            <div class="w-full relative">
                <div class="w-full h-48 overflow-hidden rounded-t-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                        <rect fill="#ffffff" width="540" height="450"></rect>
                        <defs>
                            <linearGradient id="a" gradientUnits="userSpaceOnUse" x1="0" x2="0" y1="0" y2="100%" gradientTransform="rotate(222,648,379)">
                                <stop offset="0" stop-color="#ffffff" />
                                <stop offset="1" stop-color="#FC726E" />
                            </linearGradient>
                        </defs>
                        <rect x="0" y="0" fill="url(#a)" width="100%" height="100%"></rect>
                    </svg>
                </div>

                {{-- Avatar (centered overlapping) --}}
                <div class="absolute left-1/2 -bottom-14 transform -translate-x-1/2 w-28 h-28 rounded-full bg-white border-4 border-gray-200 dark:border-gray-700 shadow-md flex items-center justify-center overflow-hidden">
                    @if(auth()->user()->avatar && file_exists(public_path(auth()->user()->avatar)))
                        <img src="{{ asset(auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <svg viewBox="0 0 128 128" class="w-24 h-24 text-gray-400">
                            <path fill="currentColor" d="M64 8a56 56 0 1 0 56 56 56 56 0 0 0-56-56zm0 104a24 24 0 1 1 24-24 24 24 0 0 1-24 24z"></path>
                        </svg>
                    @endif
                </div>
            </div>

            {{-- Card Content --}}
            <div class="flex flex-col items-center mt-16 px-6 pb-6">

                {{-- Name --}}
                <div class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ auth()->user()->username }}
                </div>

                {{-- Email --}}
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ auth()->user()->email }}
                </div>

                {{-- About / Subtitle --}}
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 text-center">
                    {{ auth()->user()->about ?? 'No description available.' }}
                </div>

                {{-- Buttons --}}
                <div class="flex gap-4 mt-6">
                    <a href="/user/edit"
                        class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold shadow-md hover:from-blue-600 hover:to-blue-700 transition-colors duration-300">
                        Edit Profile
                    </a>
                    <a href="/decks"
                        class="px-5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-xl font-medium shadow-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-300">
                        View Decks
                    </a>
                </div>

            </div>

        </div>
    </div>
</x-layout>
