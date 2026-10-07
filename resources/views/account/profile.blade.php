<x-layout title="Profile">
    <x-profile-card :user="$user">
        <div class="flex flex-col items-center mt-16 px-6 pb-6">
            <div class="text-lg font-medium text-gray-900">{{ $user->username }}</div>
            <div class="mt-1 text-sm text-gray-500">{{ $user->email }}</div>
            <div class="mt-2 text-xs text-gray-500 text-center">{{ $user->about ?? 'No description available.' }}</div>

            <div class="flex gap-4 mt-6">
                <a href="{{ route('profile.edit') }}"
                    class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold shadow-md hover:from-blue-600 hover:to-blue-700 transition-colors duration-300">
                    Edit Profile
                </a>
                <a href="{{ route('decks') }}"
                    class="px-5 py-2 bg-gray-100 text-gray-800 rounded-xl font-medium shadow-sm hover:bg-gray-200 transition-colors duration-300">
                    View Decks
                </a>
            </div>
        </div>
    </x-profile-card>
</x-layout>
