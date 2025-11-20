@props(['class' => ''])
<button class="rounded-md px-3 py-2 text-sm font-medium bg-gray-900 text-white {{ $class }}"
    aria-current="page" {{ $attributes }}>
    {{ $slot }}
</button>