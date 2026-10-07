@props(['active' => false])
<a {{ $attributes->class([
    'block md:inline-block rounded-md px-3 py-2 text-sm font-medium',
    'bg-slate-900 text-white' => $active,
    'text-slate-300 hover:bg-white/5 hover:text-white' => ! $active,
]) }} @if ($active) aria-current="page" @endif>{{ $slot }}</a>
