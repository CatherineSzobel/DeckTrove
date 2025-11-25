<x-layout :js="['resources/js/carousel.js']">
    <x-slot:header>
        Register
    </x-slot:header>

    <div class="flex items-start justify-center min-h-screen gap-8 px-4 bg-gray-50">
        <!-- Left Panel: Form -->
        <form method="POST" action="/register" class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg text-center">
            @csrf

            <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Register Account</h2>
            <p class="mt-2 text-sm text-gray-600">Create your Decktrove account</p>

            <div class="mt-8 space-y-6">
                <x-form-field>
                    <x-form-label for="username" class="block text-center">Username</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="username" id="username" required class="mx-auto" />
                        <x-form-error name="username" />
                    </div>
                </x-form-field>

                <x-form-field>
                    <x-form-label for="email" class="block text-center">E-mail</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="email" id="email" type="email" required class="mx-auto" />
                        <x-form-error name="email" />
                    </div>
                </x-form-field>

                <x-form-field>
                    <x-form-label for="password" class="block text-center">Password</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="password" id="password" type="password" required class="mx-auto" />
                        <x-form-error name="password" />
                    </div>
                </x-form-field>

                <x-form-field>
                    <x-form-label for="password_confirmation" class="block text-center">Confirm Password</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="password_confirmation" id="password_confirmation" type="password" required class="mx-auto" />
                        <x-form-error name="password_confirmation" />
                    </div>
                </x-form-field>
            </div>

            <p class="mt-2 text-sm text-gray-600">Already have an account? <a href="/login" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Login</a></p>

            <div class="mt-6 flex items-center justify-center gap-4">
                <x-form-button>Register</x-form-button>
            </div>
        </form>
        <x-welcome-div heading="Why Join Decktrove?"></x-welcome-div>
    </div>
</x-layout>