@props(['name', 'bag' => 'default'])
@error($name, $bag)
<p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
@enderror
