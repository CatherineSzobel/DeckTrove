@props(['label', 'count', 'size' => 'sm', 'live' => false])

{{-- The inside of a like button: heart, label (large size only) and count. `live` binds them to likeButton's state. --}}
@if ($size === 'lg')
<span class="inline-flex items-center gap-2">
    <x-heroicon-o-heart class="h-5 w-5 group-aria-pressed:fill-current" aria-hidden="true" />
    <span @if ($live) x-text="liked ? 'Liked' : 'Like'" @endif>{{ $label }}</span>
</span>
<span class="rounded-lg bg-canvas px-2.5 py-1.5 font-mono text-sm text-ink" @if ($live) x-text="count" @endif>{{ $count }}</span>
@else
<x-heroicon-o-heart class="h-5 w-5 group-aria-pressed:fill-current" aria-hidden="true" />
<span @if ($live) x-text="count" @endif>{{ $count }}</span>
@endif
