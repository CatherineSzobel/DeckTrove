{{-- One comment on a profile: the author, when, and the text (escaped; line breaks kept, no links). --}}
<article class="flex gap-3 border-b border-line py-4 last:border-b-0">
    <x-avatar :user="$comment->author" class="h-10 w-10 shrink-0 font-display text-lg font-extrabold" />
    <div class="flex min-w-0 flex-1 flex-col gap-1">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <a href="{{ route('users.show', $comment->author) }}" class="focus-ring break-all font-bold text-ink hover:text-accent-ink">{{ $comment->author->username }}</a>
            @if ($byOwner)
            <span class="owner-badge">Profile owner</span>
            @endif
            <time datetime="{{ $iso }}" title="{{ $date }}" class="text-[13px] text-muted">{{ $ago }}</time>
        </div>
        <p class="whitespace-pre-line break-words text-[15px] leading-relaxed text-body">{{ $comment->body }}</p>
    </div>
    @if ($canDelete)
    <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="shrink-0">
        @csrf
        @method('DELETE')
        <button type="submit" aria-label="Delete comment by {{ $comment->author->username }}"
            data-confirm="This deletes the comment. It cannot be undone." data-confirm-title="Delete this comment?" data-confirm-button="Delete comment"
            class="focus-ring flex min-h-11 items-center rounded-sm px-2 text-sm font-bold text-muted hover:text-danger">Delete</button>
    </form>
    @endif
</article>
