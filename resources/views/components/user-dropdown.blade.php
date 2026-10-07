@php $avatar = auth()->user()->avatarUrl() ?? Vite::asset('resources/img/default-icon.png'); @endphp

<div class="relative" id="userDropdown-container">
    <button type="button" id="userDropdown-btn" aria-haspopup="true" aria-expanded="false" aria-controls="userDropdown-menu"
        class="mt-2 w-10 h-10 rounded-full overflow-hidden ring-2 ring-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <img src="{{ $avatar }}" alt="Account menu" class="w-full h-full object-cover">
    </button>

    <div id="userDropdown-menu" class="hidden absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-lg z-50">
        <a href="{{ route('profile') }}" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
        <a href="{{ route('decks') }}" class="block px-4 py-2 hover:bg-gray-100">My decks</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Logout</button>
        </form>
    </div>
</div>
