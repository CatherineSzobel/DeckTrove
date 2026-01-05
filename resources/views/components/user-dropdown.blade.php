@props([
'id' => 'userDropdown',
'avatar' => Vite::asset('resources/img/default-icon.png'),
'options' => [
'profile' => 'Profile',
'decks' => 'Decks',
'logout' => 'Logout'
]
])

<div class="relative" id="{{ $id }}-container">
    <button id="{{ $id }}-btn"
        class="mt-2 w-10 h-10 rounded-full overflow-hidden ring-2 ring-gray-300 dark:ring-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
    </button>

    <div id="{{ $id }}-menu" class="hidden absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-lg z-50">
        @foreach($options as $value => $label)
        @if($value === 'logout')
        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-100">{{ $label }}</button>
        </form>
        @else
        <a href="/{{ $value }}" class="block px-4 py-2 hover:bg-gray-100">{{ $label }}</a>
        @endif
        @endforeach
    </div>
</div>