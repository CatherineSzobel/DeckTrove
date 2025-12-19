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

                {{-- Avatar (centered, clickable for preview) --}}
                <div class="absolute left-1/2 -bottom-14 transform -translate-x-1/2 w-28 h-28 rounded-full bg-white border-4 border-gray-200 dark:border-gray-700 shadow-md overflow-hidden flex items-center justify-center">
                    <label for="avatar" class="w-full h-full cursor-pointer">
                        @if($user->avatar && file_exists(public_path($user->avatar)))
                        <img id="avatarPreview" src="{{ asset($user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                        <img id="avatarPreview" src="{{ asset('default-avatar.png') }}" alt="Avatar" class="w-full h-full object-cover">
                        @endif
                    </label>
                </div>
            </div>

            {{-- Form --}}
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                class="flex flex-col items-center mt-16 px-6 pb-6 w-full">
                @csrf
                @method('PUT')

                {{-- Username --}}
                <div class="w-full mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                    <input type="text" name="username" id="username"
                        value="{{ old('username', $user->username) }}"
                        class="w-full px-4 py-2 border rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('username')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- About --}}
                <div class="w-full mb-4">
                    <label for="about" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">About</label>
                    <textarea name="about" id="about" rows="3"
                        class="w-full px-4 py-2 border rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('about', $user->about) }}</textarea>
                    @error('about')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Avatar Input --}}
                <input type="file" name="avatar" id="avatar" class="hidden" accept="image/*">

                {{-- Submit --}}
                <button type="submit"
                    class="px-6 py-2 mt-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold shadow-md hover:from-blue-600 hover:to-blue-700 transition-colors duration-300">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    {{-- Live preview script --}}
    <script>
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatarPreview');

        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Make the avatar label clickable
        document.querySelector('.avatar-wrapper label').addEventListener('click', () => {
            avatarInput.click();
        });
    </script>
</x-layout>