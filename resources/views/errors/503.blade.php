{{-- A card source is down (e.g. ScryfallUnavailableException). "Try again" reloads the same URL. --}}
<x-layout title="Temporarily unavailable">
    <x-empty-state title="Temporarily unavailable" heading="h1" class="mx-auto mt-12 max-w-xl">
        {{ $message ?? 'This page is temporarily unavailable. Please try again in a moment.' }}
        <x-slot:actions>
            <a href="{{ url()->full() }}" class="btn-primary">Try again</a>
        </x-slot:actions>
    </x-empty-state>
</x-layout>
