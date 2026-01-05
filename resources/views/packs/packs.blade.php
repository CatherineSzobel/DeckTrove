<x-layout>
    <div class="container mx-auto px-4" data-series="{{ $series }}">
        <x-database-header title="Packs">

            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div class="flex gap-2">
                    @foreach(['full' => 'Full', 'list' => 'List'] as $key => $label)
                    <a href="{{ request()->fullUrlWithQuery(['view' => $key, 'page' => 1]) }}"
                        class="px-3 py-1 rounded text-sm {{ $currentView === $key ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>

                <x-search-button placeholder="Search pack...">Search</x-search-button>
            </div>

        </x-database-header>

    </div>
    <div class="container mx-auto">
        <div class="flex justify-center w-full">
            <div class="w-full ">
                @include("packs.partials.packs-{$currentView}", ['packs' => $packs])
            </div>
        </div>

        @if($packs->hasPages())
        <div class="mt-16 flex justify-center">
            <div class="rounded-2xl bg-white shadow-sm
                px-10 py-8">
                {{ $packs->links() }}
            </div>
        </div>
        @endif
    </div>

</x-layout>