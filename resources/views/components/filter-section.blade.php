@props([
    'series' => '',
    'options' => [[], [], [], []],
    'layout' => 'w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500'
])

@php
    $filterConfig = match($series) {
        'yugioh' => [
            'ids' => ['filter-type', 'filter-attribute', 'filter-race', 'filter-archetype'],
            'labels' => ['Select Type', 'Select Attribute', 'Select Race', 'Select Archetype'],
            'keys' => ['type', 'attribute', 'race', 'archetype']
        ],
        'magic' => [
            'ids' => ['filter-type', 'filter-color', 'filter-rarity', 'filter-set_name'],
            'labels' => ['Select Type', 'Select Color', 'Select Rarity', 'Select Set'],
            'keys' => ['type', 'color', 'rarity', 'set_name']
        ],
        default => [
            'ids' => ['filter-type', 'filter-attribute', 'filter-race', 'filter-archetype'],
            'labels' => ['Select Type', 'Select Attribute', 'Select Race', 'Select Archetype'],
            'keys' => ['type', 'attribute', 'race', 'archetype']
        ]
    };
@endphp

<section
    id="filterDetails"
    class="hidden w-full flex flex-col items-center justify-center gap-4 mt-4 transition-all duration-300">
    <div id="filter-container" class="flex flex-wrap gap-4 my-4">
        <div id="active-filters" class="flex gap-2 flex-wrap mb-4"></div>
        
        @foreach($filterConfig['ids'] as $index => $id)
            <div class="flex flex-col">
                <label for="{{ $id }}" class="text-sm font-medium text-gray-700 mb-1">
                    {{ $filterConfig['labels'][$index] }}
                </label>
                <select 
                    id="{{ $id }}"
                    name="{{ $filterConfig['keys'][$index] }}"
                    class="{{ $layout }} filter-select"
                    data-filter-key="{{ $filterConfig['keys'][$index] }}">
                    <option value="">All {{ $filterConfig['labels'][$index] }}</option>
                    @foreach($options[$index] ?? [] as $option)
                        <option value="{{ $option }}" 
                            {{ request($filterConfig['keys'][$index]) == $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</section>