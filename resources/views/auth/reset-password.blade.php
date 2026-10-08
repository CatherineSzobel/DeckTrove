<x-layout title="Reset password">
    <div class="flex items-start justify-center gap-8 px-4">
        <form method="POST" action="{{ route('password.store') }}" class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg text-center">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Choose a new password</h2>

            <div class="mt-8 space-y-6">
                <x-form-field>
                    <x-form-label for="email" class="block text-center">E-mail</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="email" id="email" type="email" :value="old('email', $email)" autocomplete="email" required class="mx-auto" />
                        <x-form-error name="email" />
                    </div>
                </x-form-field>

                <x-form-field>
                    <x-form-label for="password" class="block text-center">New password</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="password" id="password" type="password" autocomplete="new-password" required minlength="8" class="mx-auto" />
                        <x-form-error name="password" />
                    </div>
                </x-form-field>

                <x-form-field>
                    <x-form-label for="password_confirmation" class="block text-center">Confirm new password</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password" required class="mx-auto" />
                    </div>
                </x-form-field>
            </div>

            <div class="mt-6 flex items-center justify-center gap-4">
                <x-form-button>Reset password</x-form-button>
            </div>
        </form>
    </div>
</x-layout>
