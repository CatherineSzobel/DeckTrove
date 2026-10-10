@props(['user'])

{{-- The header's account button: the user's avatar (or initial) opening a small menu. --}}
@php $avatar = $user->avatarUrl(); @endphp

<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
    <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-expanded="false" aria-haspopup="true"
        aria-controls="userMenu" aria-label="Account menu"
        class="focus-ring flex h-11 items-center gap-2 rounded-full border border-line pl-1.5 pr-2.5 text-ink">
        @if ($avatar)
        <img src="{{ $avatar }}" alt="" class="h-8 w-8 rounded-full object-cover">
        @else
        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-accent text-sm font-bold text-white">{{ Str::upper(Str::substr($user->username, 0, 1)) }}</span>
        @endif
        <x-heroicon-o-chevron-down class="h-4 w-4" />
    </button>

    <div id="userMenu" x-cloak x-show="open" x-transition.opacity
        class="absolute right-0 z-50 mt-2 w-48 overflow-hidden rounded-xl border border-line bg-surface py-1 shadow-xl">
        <a href="{{ route('dashboard') }}" class="focus-ring block px-4 py-2.5 text-body hover:bg-surface-2 hover:text-ink">Dashboard</a>
        <a href="{{ route('decks') }}" class="focus-ring block px-4 py-2.5 text-body hover:bg-surface-2 hover:text-ink">My decks</a>
        <a href="{{ route('profile') }}" class="focus-ring block px-4 py-2.5 text-body hover:bg-surface-2 hover:text-ink">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="focus-ring block w-full px-4 py-2.5 text-left text-body hover:bg-surface-2 hover:text-ink">Log out</button>
        </form>
    </div>
</div>
