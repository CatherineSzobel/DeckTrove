<x-layout title="Edit profile" :js="['resources/js/account.js']">
    <x-profile-card :user="$user" editable>
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
            class="flex flex-col items-center mt-16 px-6 pb-6 w-full">
            @csrf
            @method('PUT')

            <p class="text-xs text-gray-500 mb-4">Click the avatar to choose a new picture (JPG, PNG or WebP, max 2 MB).</p>
            <input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg,image/webp" class="sr-only">
            <x-form-error name="avatar" />

            <div class="w-full mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                <input type="text" name="username" id="username" required maxlength="30"
                    value="{{ old('username', $user->username) }}"
                    class="w-full px-4 py-2 border rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <x-form-error name="username" />
            </div>

            <div class="w-full mb-4">
                <label for="about" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">About</label>
                <textarea name="about" id="about" rows="3" maxlength="500"
                    class="w-full px-4 py-2 border rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('about', $user->about) }}</textarea>
                <x-form-error name="about" />
            </div>

            <button type="submit"
                class="px-6 py-2 mt-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold shadow-md hover:from-blue-600 hover:to-blue-700 transition-colors duration-300">
                Save Changes
            </button>
        </form>
    </x-profile-card>
</x-layout>
