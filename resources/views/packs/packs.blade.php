<x-layout :title="config('series.'.$series.'.label').' packs'">
    <div class="container mx-auto px-4">
        <x-database-header :title="config('series.'.$series.'.label').' packs'">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div class="flex gap-2" role="group" aria-label="Pack view">
                    @foreach (['full' => 'Full', 'list' => 'List'] as $key => $label)
                    <a href="{{ request()->fullUrlWithQuery(['view' => $key, 'page' => null]) }}" @if ($currentView === $key) aria-current="page" @endif
                        class="px-3 py-1 rounded text-sm {{ $currentView === $key ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>

                <x-search-button placeholder="Search packs by name or code..." :value="$search" :hidden="['view' => $currentView]">Search</x-search-button>
            </div>

            @if ($search !== '')
            <p class="text-sm text-gray-600">
                {{ number_format($packs->total()) }} {{ Str::plural('pack', $packs->total()) }} matching “{{ $search }}” ·
                <a href="{{ route('packs.index', [$series, 'view' => $currentView]) }}" class="text-blue-600 underline">clear</a>
            </p>
            @endif
        </x-database-header>
    </div>

    <div class="container mx-auto">
        @if ($packs->isEmpty())
        <p class="text-center text-gray-500 py-16">No packs found.</p>
        @else
        @include("packs.partials.packs-{$currentView}")
        @endif

        @if ($packs->hasPages())
        <div class="mt-16 flex justify-center">
            <div class="rounded-2xl bg-white shadow-sm px-10 py-8">{{ $packs->links() }}</div>
        </div>
        @endif
    </div>
</x-layout>
