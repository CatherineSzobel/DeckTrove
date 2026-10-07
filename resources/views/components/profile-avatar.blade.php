@props(['user', 'editable' => false])

@php $avatar = $user->avatarUrl() ?? Vite::asset('resources/img/default-icon.png'); @endphp

@if ($editable)
<label for="avatar" class="w-full h-full cursor-pointer" title="Change avatar">
    <img id="avatarPreview" src="{{ $avatar }}" alt="Avatar" class="w-full h-full object-cover" />
</label>
@else
<img src="{{ $avatar }}" alt="Avatar" class="w-full h-full object-cover">
@endif
