@props([
    'series',
    'options' => [],
    'layout' => 'w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
])

{{-- Filter dropdowns for a series, as defined by `filters` in config/series.php. --}}
<section id="filterDetails" class="hidden w-full flex flex-col items-center gap-4 mt-4 transition-all duration-300">
    <div id="filter-container" class="flex flex-wrap gap-4 my-4">
        @foreach (config("series.$series.filters") as $key => $filter)
        @php
            $values = $options[$key] ?? [];
            // Lists of values become value => label maps; config may provide nicer labels.
            $choices = array_is_list($values) ? array_combine($values, $values) : $values;
            $labels = $filter['labels'] ?? [];
        @endphp
        <div class="flex flex-col">
            <label for="filter-{{ $key }}" class="text-sm font-medium text-gray-700 mb-1">{{ $filter['label'] }}</label>
            <select id="filter-{{ $key }}" name="{{ $key }}" class="{{ $layout }} filter-select">
                <option value="">All</option>
                @foreach ($choices as $value => $label)
                <option value="{{ $value }}" @selected((string) request($key) === (string) $value)>{{ $labels[$value] ?? $label }}</option>
                @endforeach
            </select>
        </div>
        @endforeach
    </div>
</section>
