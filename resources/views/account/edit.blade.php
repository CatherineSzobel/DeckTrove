<x-layout :js="['resources/js/account.js']">

    <x-profile-card type="edit" :user="$user">

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
            class="flex flex-col items-center mt-16 px-6 pb-6 w-full">
            @csrf
            @method('PUT')

            <div class="w-full mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                <input type="text" name="username" id="username"
                    value="{{ old('username', $user->username) }}"
                    class="w-full px-4 py-2 border rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('username')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="w-full mb-4">
                <label for="about" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">About</label>
                <textarea name="about" id="about" rows="3"
                    class="w-full px-4 py-2 border rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('about', $user->about) }}</textarea>
                @error('about')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <input type="file" name="avatar" id="avatar">

            <button type="submit"
                class="px-6 py-2 mt-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold shadow-md hover:from-blue-600 hover:to-blue-700 transition-colors duration-300">
                Save Changes
            </button>
        </form>
    </x-profile-card>
</x-layout>