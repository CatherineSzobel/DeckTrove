{{-- Log in: a plain POST form (SessionController@store) next to the "About DeckTrove" showcase. --}}

<x-layout title="Log in">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-16 px-4 py-12 sm:px-6 lg:py-[72px]">
        <x-auth.card eyebrow="Welcome back" title="Log in">
            <x-slot:intro><p class="text-base text-body">Log in to your DeckTrove account.</p></x-slot:intro>

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
                @csrf
                <x-field name="username" label="Username" :value="old('username')" autocomplete="username" required />
                <x-field name="password" label="Password" type="password" autocomplete="current-password" required />

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="flex min-h-11 cursor-pointer items-center gap-2.5 text-[15px] text-body">
                        <input type="checkbox" name="remember" value="1" class="focus-ring h-5 w-5 accent-accent">
                        Keep me logged in
                    </label>
                    <a href="{{ route('password.request') }}" class="focus-ring inline-flex min-h-11 items-center rounded-sm text-sm font-bold text-accent-ink">Forgot your password?</a>
                </div>

                <button type="submit" class="btn-primary mt-1 h-[52px] w-full text-[17px]">Log in</button>
            </form>

            <x-slot:footer>Don’t have an account? <a href="{{ route('register') }}" class="focus-ring rounded-sm font-bold text-accent-ink">Create one</a></x-slot:footer>
        </x-auth.card>

        <x-auth.showcase variant="login" />
    </div>
</x-layout>
