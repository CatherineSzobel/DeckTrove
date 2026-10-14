{{-- Reset password: the link from the e-mail lands here with its token (PasswordResetController@update). --}}

<x-layout title="Reset password">
    <div class="mx-auto flex max-w-7xl justify-center px-4 py-12 sm:px-6 lg:py-[72px]">
        <x-auth.card eyebrow="Account help" title="Choose a new password">
            <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-[18px]">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <x-field name="email" label="E-mail" type="email" :value="old('email', $email)" autocomplete="email" required />

                <div class="flex flex-wrap gap-3">
                    <div class="min-w-0 flex-[1_1_160px]">
                        <x-field name="password" label="New password" type="password" autocomplete="new-password" minlength="8" required />
                    </div>
                    <div class="min-w-0 flex-[1_1_160px]">
                        <x-field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required />
                    </div>
                </div>

                <button type="submit" class="btn-primary mt-1 h-[52px] w-full text-[17px]">Reset password</button>
            </form>

            <x-slot:footer><a href="{{ route('login') }}" class="focus-ring rounded-sm font-bold text-accent-ink">Back to log in</a></x-slot:footer>
        </x-auth.card>
    </div>
</x-layout>
