@props(['name', 'bag' => 'default'])

{{-- The first validation error for a field, read out as soon as it appears. x-field links it to its input by id. --}}
{{-- The optional id is an attribute (left out when null); x-field passes it. --}}
@error($name, $bag)
<p {{ $attributes->merge(['id' => null, 'role' => 'alert', 'class' => 'flex items-center gap-1.5 text-sm text-danger']) }}>
    <x-heroicon-o-exclamation-circle class="h-4 w-4 shrink-0" />
    {{ $message }}
</p>
@enderror
