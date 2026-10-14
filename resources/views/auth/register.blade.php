{{-- Create account: a plain POST form (RegisterUserController@store) next to the "About DeckTrove" showcase. --}}

<x-layout title="Create account">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-16 px-4 py-12 sm:px-6 lg:py-[72px]">
        <x-auth.card eyebrow="Join DeckTrove" title="Create account">
            <x-slot:intro><p class="text-base text-body">Save your decks and share them with the community.</p></x-slot:intro>

            <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-[18px]">
                @csrf
                <x-field name="username" label="Username" :value="old('username')" autocomplete="username" required />
                <x-field name="email" label="E-mail" type="email" :value="old('email')" autocomplete="email" required />

                {{-- The password pair sits side by side when there's room; each wrapper is one flex column. --}}
                <div class="flex flex-wrap gap-3">
                    <div class="min-w-0 flex-[1_1_160px]">
                        <x-field name="password" label="Password" type="password" autocomplete="new-password" required />
                    </div>
                    <div class="min-w-0 flex-[1_1_160px]">
                        <x-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required />
                    </div>
                </div>

                <button type="submit" class="btn-primary mt-1 h-[52px] w-full text-[17px]">Create account</button>
            </form>

            <x-slot:footer>Already have an account? <a href="{{ route('login') }}" class="focus-ring rounded-sm font-bold text-accent-ink">Log in</a></x-slot:footer>
        </x-auth.card>

        <x-auth.showcase variant="register" />
    </div>
</x-layout>
