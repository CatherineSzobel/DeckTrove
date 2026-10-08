<x-layout title="Forgot password">
    <div class="flex items-start justify-center gap-8 px-4">
        <form method="POST" action="{{ route('password.email') }}" class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg text-center">
            @csrf

            <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Forgot your password?</h2>
            <p class="mt-2 text-sm text-gray-600">Enter your account's email and we'll send you a link to choose a new one.</p>

            <div class="mt-8 space-y-6">
                <x-form-field>
                    <x-form-label for="email" class="block text-center">E-mail</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="email" id="email" type="email" :value="old('email')" autocomplete="email" required class="mx-auto" />
                        <x-form-error name="email" />
                    </div>
                </x-form-field>
            </div>

            <div class="mt-6 flex items-center justify-center gap-4">
                <x-form-button>Send reset link</x-form-button>
            </div>

            <p class="mt-6 text-sm text-gray-600">
                Remembered it? <a href="{{ route('login') }}" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Back to login</a>
            </p>
        </form>
    </div>
</x-layout>
