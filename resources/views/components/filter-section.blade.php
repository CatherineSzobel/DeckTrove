@props([
'series' => '',
'options' => [[], [], [], []],
'layout' => 'w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500'
])
@php
$filterConfig = match($series) {
'yugioh' => [
['id'=>'filter-type', 'label'=>'Type', 'key'=>'type', 'type'=>'select'],
['id'=>'filter-attribute', 'label'=>'Attribute', 'key'=>'attribute', 'type'=>'select'],
['id'=>'filter-race', 'label'=>'Race', 'key'=>'race', 'type'=>'select'],
['id'=>'filter-archetype', 'label'=>'Archetype', 'key'=>'archetype', 'type'=>'select'],
],
'magic' => [
['id'=>'filter-type', 'label'=>'Type', 'key'=>'type', 'type'=>'select'],
['id'=>'filter-color', 'label'=>'Color', 'key'=>'color', 'type'=>'color'],
['id'=>'filter-rarity', 'label'=>'Rarity', 'key'=>'rarity', 'type'=>'select'],
['id'=>'filter-set_name', 'label'=>'Set', 'key'=>'set_name', 'type'=>'keyvalue'],
],
default => [
['id'=>'filter-type', 'label'=>'Type', 'key'=>'type', 'type'=>'select'],
['id'=>'filter-attribute', 'label'=>'Attribute', 'key'=>'attribute', 'type'=>'select'],
['id'=>'filter-race', 'label'=>'Race', 'key'=>'race', 'type'=>'select'],
['id'=>'filter-archetype', 'label'=>'Archetype', 'key'=>'archetype', 'type'=>'select'],
]
};
@endphp
<section id="filterDetails" class="hidden w-full flex flex-col items-center gap-4 mt-4 transition-all duration-300">
    <div id="filter-container" class="flex flex-wrap gap-4 my-4">
        @foreach($filterConfig as $index => $filter)
        <div class="flex flex-col">
            <label for="{{ $filter['id'] }}" class="text-sm font-medium text-gray-700 mb-1">
                {{ $filter['label'] }}
            </label>

            @php $optionsList = $options[$index] ?? []; @endphp

            @switch($filter['type'])
            @case('color')
            <select id="{{ $filter['id'] }}" name="{{ $filter['key'] }}" class="{{ $layout }} filter-select">
                <option value="">All Colors</option>
                @foreach($optionsList as $color)
                @php
                $colorMap = ['W'=>'White','U'=>'Blue','B'=>'Black','R'=>'Red','G'=>'Green', 'C'=>'Colorless'];
                $label = $colorMap[$color] ?? $color;
                @endphp
                <option value="{{ $color }}" {{ request($filter['key']) === $color ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
            @break

            @case('keyvalue')
            <select id="{{ $filter['id'] }}" name="{{ $filter['key'] }}" class="{{ $layout }} filter-select">
                <option value="">All {{ $filter['label'] }}</option>
                @foreach($optionsList as $value => $label)
                <option value="{{ $value }}" {{ request($filter['key']) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
            @break

            @default
            <select id="{{ $filter['id'] }}" name="{{ $filter['key'] }}" class="{{ $layout }} filter-select">
                <option value="">All {{ $filter['label'] }}</option>
                @foreach($optionsList as $option)
                <option value="{{ $option }}" {{ request($filter['key']) == $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
                @endforeach
            </select>
            @endswitch
        </div>
        @endforeach
    </div>
</section>