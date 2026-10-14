{{-- A labelled input (App\View\Components\Field); other attributes (autocomplete, required, …) go on the input. --}}
<div class="flex flex-col gap-2">
    <label for="{{ $inputId }}" class="text-sm font-bold text-ink">{{ $label }}</label>
    <input id="{{ $inputId }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}"
        @error($name, $bag) aria-invalid="true" aria-describedby="{{ $errorId }}" @enderror
        {{ $attributes->class('input h-[50px] w-full text-base') }}>
    <x-form-error :name="$name" :bag="$bag" :id="$errorId" />
</div>
