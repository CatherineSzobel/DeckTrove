@props(['profile', 'draft', 'length', 'counter'])

{{-- Leave a comment on a profile: a plain POST (works without JS) with a live counter (charCounter). --}}
<form method="POST" action="{{ route('users.comments.store', $profile) }}" x-data="charCounter({{ $length }})" class="flex flex-col gap-3">
    @csrf

    <div class="flex items-baseline justify-between gap-2">
        <label for="comment-body" class="text-sm font-bold text-ink">Leave a comment</label>
        <span class="font-mono text-xs text-muted" x-text="counter">{{ $counter }}</span>
    </div>
    <textarea id="comment-body" name="body" rows="3" maxlength="500" required @error('body', 'comment') aria-invalid="true" aria-describedby="comment-body-error" @enderror
        @input="count($event)" class="input w-full resize-y leading-normal">{{ $draft }}</textarea>
    <x-form-error name="body" bag="comment" id="comment-body-error" />

    <div class="flex justify-end">
        <button type="submit" class="btn-primary">Post comment</button>
    </div>
</form>
