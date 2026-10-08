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
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" id="username" required maxlength="30"
                    value="{{ old('username', $user->username) }}"
                    class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <x-form-error name="username" />
            </div>

            <div class="w-full mb-4">
                <label for="about" class="block text-sm font-medium text-gray-700 mb-1">About</label>
                <textarea name="about" id="about" rows="3" maxlength="500"
                    class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('about', $user->about) }}</textarea>
                <x-form-error name="about" />
            </div>

            <button type="submit"
                class="px-6 py-2 mt-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold shadow-md hover:from-blue-600 hover:to-blue-700 transition-colors duration-300">
                Save Changes
            </button>
        </form>
    </x-profile-card>

    <section class="max-w-sm mx-auto mt-8 bg-white shadow-xl rounded-2xl p-6" aria-labelledby="passwordHeading">
        <h2 id="passwordHeading" class="text-lg font-semibold text-gray-900">Change password</h2>

        <form action="{{ route('profile.password') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            @foreach (['current_password' => 'Current password', 'password' => 'New password', 'password_confirmation' => 'Confirm new password'] as $field => $label)
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                <input type="password" name="{{ $field }}" id="{{ $field }}" required
                    autocomplete="{{ $field === 'current_password' ? 'current-password' : 'new-password' }}"
                    class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <x-form-error :name="$field" bag="updatePassword" />
            </div>
            @endforeach

            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-semibold shadow-md hover:bg-blue-700">
                Change password
            </button>
        </form>
    </section>

    <section class="max-w-sm mx-auto mt-8 mb-8 bg-white shadow-xl rounded-2xl p-6 border border-red-200" aria-labelledby="deleteHeading">
        <h2 id="deleteHeading" class="text-lg font-semibold text-red-600">Delete account</h2>
        <p class="mt-2 text-sm text-gray-600">This permanently deletes your account, your decks and your avatar. It cannot be undone.</p>

        <form action="{{ route('profile.destroy') }}" method="POST" class="mt-4 space-y-4"
            data-confirm="Delete your account and all your decks? This cannot be undone.">
            @csrf
            @method('DELETE')

            <div>
                <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm with your password</label>
                <input type="password" name="password" id="delete_password" required autocomplete="current-password"
                    class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500">
                <x-form-error name="password" bag="deleteAccount" />
            </div>

            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-xl font-semibold shadow-md hover:bg-red-700">
                Delete my account
            </button>
        </form>
    </section>
</x-layout>
