@props(['user'])

{{-- A user's picture, or their initial on the accent colour. Size, text size and weight come from the class. --}}
@if ($user->avatarUrl())
<img src="{{ $user->avatarUrl() }}" alt="" {{ $attributes->class('rounded-full object-cover') }}>
@else
<span aria-hidden="true" {{ $attributes->class('flex items-center justify-center rounded-full bg-accent text-white') }}>{{ $user->initial }}</span>
@endif
