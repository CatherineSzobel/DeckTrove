@props(['canPost', 'action', 'draft'])

{{-- The comment form for logged-in players ($action: the store URL, $draft: App\Comments\CommentDraft), a log-in line for guests. --}}
@if ($canPost)
<x-comment-form :action="$action" :draft="$draft" />
@else
<p class="text-[15px] text-body"><a href="{{ route('login') }}" class="focus-ring font-bold text-accent-ink">Log in</a> to leave a comment.</p>
@endif
