@php
$series = $series ?? 'yugioh';
@endphp
<x-layout>
    <div class="container mx-auto px-4" data-series="{{ $series }}">
        <x-database-header title="Packs">

            <!-- Flex container: dropdowns left, search right -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">

                <!-- Left side: dropdowns -->
                <div class="flex gap-4 flex-wrap">

                    <!-- Dropdown 2 -->
                    <div class="relative group inline-block text-left">
                        <p> Show <x-select-dropdown
                                :class="'entries-selector'"
                                :options="[10 => 10, 25 => 25, 50 => 50, 100 => 100]"
                                :layout="'inline-flex justify-center rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700'">

                            </x-select-dropdown> entries </p>
                    </div>
                </div>

                <!-- Right side: search bar -->
                <x-search-button placeholder="Search pack...">Search</x-search-button>
            </div>

        </x-database-header>

    </div>
    <!-- Pack container: JS will populate this -->
    <div class="container mx-auto px-4">
        <div class="flex justify-center w-full">
            <div class="w-full max-w-5xl">
                @include("packs.{$series}.packs-list", ['packs' => $packs])
            </div>
        </div>


        <!-- Pagination -->
        <div class="mt-6">
            {{ $packs->appends(request()->query())->links() }}
        </div>
    </div>

</x-layout>