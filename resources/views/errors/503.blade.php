<x-layout title="Temporarily unavailable">
    <div class="max-w-xl mx-auto text-center py-24">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Temporarily unavailable</h1>
        <p class="text-gray-600 mb-8">{{ $message ?? 'This page is temporarily unavailable. Please try again in a moment.' }}</p>
        <a href="{{ url()->current() }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">Try again</a>
    </div>
</x-layout>
