@props(['type' => '', 'user' => null])
@php
    if ($type === 'edit') {
        $avatarPath = $user && $user->avatar
            ? asset('storage/' . $user->avatar)
            : asset('default-avatar.png');
    } else {
        $avatarPath = auth()->user() && auth()->user()->avatar
            ? asset('storage/' . auth()->user()->avatar)
            : null;
    }
@endphp

@if ($type === 'edit')
    <label for="avatar" class="w-full h-full cursor-pointer">
        <img
            id="avatarPreview"
            src="{{ $avatarPath }}"
            alt="Avatar"
            class="w-full h-full object-cover" />
    </label>
@else
    @if ($avatarPath)
        <img src="{{ $avatarPath }}" alt="Avatar" class="w-full h-full object-cover">
    @else
        <svg viewBox="0 0 128 128" class="w-24 h-24 text-gray-400">
            <path fill="currentColor" d="M64 8a56 56 0 1 0 56 56 56 56 0 0 0-56-56zm0 104a24 24 0 1 1 24-24 24 24 0 0 1-24 24z"></path>
        </svg>
    @endif
@endif
