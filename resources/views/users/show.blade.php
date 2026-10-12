{{-- Public profile. A bare placeholder: the "My decks + Public decks" redesign on frontend-rework replaces it. --}}
<x-layout :title="$user->username">
    <div class="max-w-7xl mx-auto px-6 py-12 space-y-10">
        <div class="flex flex-col items-center gap-3 text-center">
            @if ($user->avatarUrl())
            <img src="{{ $user->avatarUrl() }}" alt="" class="h-24 w-24 rounded-full object-cover">
            @endif
            <h1 class="text-4xl font-extrabold text-gray-900">{{ $user->username }}</h1>
            <p class="text-sm text-gray-500">Joined {{ $user->created_at->format('F Y') }}</p>
            @if ($user->about)
            <p class="max-w-xl text-gray-700">{{ $user->about }}</p>
            @endif
            <p class="text-sm text-gray-600">
                {{ $stats['public_decks'] }} public {{ Str::plural('deck', $stats['public_decks']) }} ·
                {{ $stats['likes_received'] }} {{ Str::plural('like', $stats['likes_received']) }} received
            </p>
            @if ($isOwner)
            <a href="{{ route('profile.edit') }}" class="font-medium text-blue-600 hover:text-blue-800">Edit profile</a>
            @endif
        </div>

        @include('decks.partials.deck-card', ['decksPath' => route('users.show', $user)])
    </div>
</x-layout>
