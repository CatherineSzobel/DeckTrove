{{-- The comment form for logged-in players, a log-in line for guests. --}}
@if ($page->canComment())
<x-comment-form :action="route('users.comments.store', $user)" :draft="$page->commentDraft()" />
@else
<p class="text-[15px] text-body"><a href="{{ route('login') }}" class="focus-ring font-bold text-accent-ink">Log in</a> to leave a comment.</p>
@endif
