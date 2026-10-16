{{-- Comments on a profile ($comments: a page of them or the newest few), or "No comments yet". --}}
@if ($comments->isNotEmpty())
<div class="flex flex-col rounded-3xl border border-line bg-surface px-6">
    @foreach ($comments as $comment)
    <x-profile.comment :comment="$comment" :profile="$user" />
    @endforeach
</div>
@else
<x-empty-state title="No comments yet" :heading="$emptyHeading ?? 'h3'" />
@endif
