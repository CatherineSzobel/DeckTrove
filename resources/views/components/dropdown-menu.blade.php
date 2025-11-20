@props(['class' => 'absolute right-1/2 translate-x-1/2 mt-2 w-max
origin-top-right rounded-md bg-white shadow-lg
ring-1 ring-black ring-opacity-5
opacity-0 invisible group-hover:opacity-100 group-hover:visible
transition-all duration-150
z-50'])
<div {{ $attributes->merge(['class' => $attributes->get('class', $class)]) }}>
  <div class="py-1 flex flex-col items-center space-y-1">
    {{ $slot }}
  </div>
</div>