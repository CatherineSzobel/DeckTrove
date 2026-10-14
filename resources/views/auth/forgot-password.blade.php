{{-- Forgot password: asks for the account's e-mail (PasswordResetController@store); the answer shows as a notice. --}}

<x-layout title="Forgot password">
    <div class="mx-auto flex max-w-7xl justify-center px-4 py-12 sm:px-6 lg:py-[72px]">
        <x-auth.card eyebrow="Account help" title="Forgot your password?">
            <x-slot:intro><p class="text-base text-body">Enter your account’s email and we’ll send you a link to choose a new one.</p></x-slot:intro>

            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
                @csrf
                <x-field name="email" label="E-mail" type="email" :value="old('email')" autocomplete="email" required />
                <button type="submit" class="btn-primary mt-1 h-[52px] w-full text-[17px]">Send reset link</button>
            </form>

            <x-slot:footer>Remembered it? <a href="{{ route('login') }}" class="focus-ring rounded-sm font-bold text-accent-ink">Back to log in</a></x-slot:footer>
        </x-auth.card>
    </div>
</x-layout>
