@php
$series = $series ?? 'yugioh';
$currentView = request('view', 'full');
@endphp

<x-layout>
    <div class="container mx-auto px-4" data-series="{{ $series }}">
        <x-database-header title="Packs">

            <!-- Flex container: dropdowns left, search right -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div class="flex gap-2">
                    @foreach(['full' => 'Full', 'list' => 'List'] as $key => $label)
                    <a href="{{ request()->fullUrlWithQuery(['view' => $key, 'page' => 1]) }}"
                        class="px-3 py-1 rounded text-sm {{ $currentView === $key ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>

                <!-- Right side: search bar -->
                <x-search-button placeholder="Search pack...">Search</x-search-button>
            </div>

        </x-database-header>

    </div>
    <!-- Pack container: JS will populate this -->
    <div class="container mx-auto">
        <div class="flex justify-center w-full">
            <div class="w-full ">
                @include("packs.{$series}.packs-{$currentView}", ['packs' => $packs])
            </div>
        </div>


        <!-- Pagination -->
        <div class="mt-6">
            {{ $packs->appends(request()->query())->links() }}
        </div>
    </div>

</x-layout>