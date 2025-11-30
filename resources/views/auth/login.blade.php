<x-layout :js="['resources/js/carousel.js']">
    <x-slot:header>
        Register
    </x-slot:header>

    <div class="flex items-start justify-center min-h-screen gap-8 px-4 bg-gray-50">
        <!-- Left Panel: Form -->
        <form method="POST" action="/login" class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg text-center">
            @csrf

            <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Log-in</h2>
            <p class="mt-2 text-sm text-gray-600">Log in to your Decktrove account</p>

            <div class="mt-8 space-y-6">
                <x-form-field>
                    <x-form-label for="username" class="block text-center">Username</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="username" id="username" required class="mx-auto" />
                        <x-form-error name="username" />
                    </div>
                </x-form-field>

                <x-form-field>
                    <x-form-label for="password" class="block text-center">Password</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="password" id="password" type="password" required class="mx-auto" />
                        <x-form-error name="password" />
                    </div>
                </x-form-field>

            </div>

            <p class="mt-2 text-sm text-gray-600">You don't have an account? <a href="/register" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Register</a></p>

            <div class="mt-6 flex items-center justify-center gap-4">
                <x-form-button>Login</x-form-button>
            </div>
        </form>

        <!-- Right Panel: Non-form content -->
        <x-welcome-div heading="Welcome to DeckTrove!"></x-welcome-div>
    </div>
</x-layout>