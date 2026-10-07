{{-- Toggles the filter panel (#filterDetails); behaviour lives in resources/js/filter.js. --}}
<button type="button" id="filter-button" aria-controls="filterDetails" aria-expanded="false"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600']) }}>
    <x-heroicon-o-funnel class="h-5 w-5" />
    <span data-label>Filter</span>
</button>
