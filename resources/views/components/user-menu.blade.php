@props(['user'])

{{-- The header's account button: the user's avatar (or initial) opening a small menu. --}}
<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="if (open) { open = false; $refs.button.focus() }" class="relative">
    <button type="button" x-ref="button" @click="open = ! open" :aria-expanded="open.toString()" aria-expanded="false" aria-haspopup="true"
        aria-controls="userMenu" aria-label="Account menu"
        class="focus-ring flex h-11 items-center gap-2 rounded-full border border-line pl-1.5 pr-2.5 text-ink">
        <x-avatar :user="$user" class="h-8 w-8 text-sm font-bold" />
        <x-heroicon-o-chevron-down class="h-4 w-4" />
    </button>

    <div id="userMenu" x-cloak x-show="open" x-transition.opacity
        class="absolute right-0 z-50 mt-2 w-48 rounded-xl border border-line bg-surface p-1 shadow-xl">
        <a href="{{ route('dashboard') }}" class="focus-ring block rounded-lg px-3 py-2.5 text-body hover:bg-surface-2 hover:text-ink">Dashboard</a>
        <a href="{{ route('decks') }}" class="focus-ring block rounded-lg px-3 py-2.5 text-body hover:bg-surface-2 hover:text-ink">My decks</a>
        <a href="{{ route('profile') }}" class="focus-ring block rounded-lg px-3 py-2.5 text-body hover:bg-surface-2 hover:text-ink">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="focus-ring block w-full rounded-lg px-3 py-2.5 text-left text-body hover:bg-surface-2 hover:text-ink">Log out</button>
        </form>
    </div>
</div>
