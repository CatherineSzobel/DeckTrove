@props(['user', 'editable' => false])

{{-- The account page's avatar (the default icon when there's none); editable, it opens the file picker. --}}
@if ($editable)
<label for="avatar" class="w-full h-full cursor-pointer" title="Change avatar">
@endif
    <img @if ($editable) id="avatarPreview" @endif src="{{ $user->avatarUrl() ?? Vite::asset('resources/img/default-icon.png') }}" alt="Avatar" class="w-full h-full object-cover">
@if ($editable)
</label>
@endif
