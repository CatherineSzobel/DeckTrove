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
    <!-- Avatar button -->
     
    <button id="{{ $id }}-btn" class="flex items-center rounded-full w-10 h-10 p-1 rounded-full ring-2 ring-default focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <img class="h-10 w-10 rounded-full object-cover" src="{{ $avatar }}" alt="User Avatar">
    </button>

    <!-- Dropdown menu -->
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