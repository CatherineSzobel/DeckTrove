@props([
'optgroup' => 'Select Series',
'options' => [],
'class' => '',
'id' => '',
'layout' => 'mt-2 px-2 py-1 rounded border text-xs text-gray-400'
])

<select class="{{ $class }} {{ $layout }}" id="{{ $id }}">

    <?php if ($optgroup === 'NONE') : ?>

        @foreach ($options as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
        @endforeach

    <?php else : ?>

        <optgroup label="{{ $optgroup }}">
            @foreach ($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
            
        </optgroup>
    <?php endif; ?>
</select>