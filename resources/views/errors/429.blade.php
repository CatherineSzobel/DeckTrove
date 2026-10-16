{{-- Too many requests (e.g. the card-search rate limit in AppServiceProvider). "Try again" reloads the same URL. --}}
<x-layout title="Slow down a little">
    <x-empty-state title="Slow down a little" heading="h1" class="mx-auto mt-12 max-w-xl">
        You’re searching faster than we can keep up with. Wait a minute, then try again.
        <x-slot:actions>
            <a href="{{ url()->full() }}" class="btn-primary">Try again</a>
        </x-slot:actions>
    </x-empty-state>
</x-layout>
