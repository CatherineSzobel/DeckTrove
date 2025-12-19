@props([
'optgroup' => 'Select Series',
'options' => [],
'class' => '',
'id' => '',
'layout' => 'mt-2 px-2 py-1 rounded border text-xs text-gray-400',
'activeList' => [true, true, false, false]
])

<select class="{{ $class }} {{ $layout }}" id="{{ $id }}">
    @if ($optgroup !== 'NONE')
    <optgroup label="{{ $optgroup }}">
        @endif

        @foreach ($options as $value => $label)
        <option
            value="{{ $value }}"
            @disabled($activeList[$value] ?? false)>
            {{ $label }}
        </option>
        @endforeach

        @if ($optgroup !== 'NONE')
    </optgroup>
    @endif
</select>