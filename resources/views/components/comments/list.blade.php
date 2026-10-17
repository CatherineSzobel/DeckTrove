@props(['comments', 'ownerId', 'ownerBadge', 'emptyHeading' => 'h3'])

{{-- Comments on a profile or deck (a page of them or the newest few), or "No comments yet". --}}
@if ($comments->isNotEmpty())
<div class="flex flex-col rounded-3xl border border-line bg-surface px-6">
    @foreach ($comments as $comment)
    <x-comment :comment="$comment" :owner-id="$ownerId" :owner-badge="$ownerBadge" />
    @endforeach
</div>
@else
<x-empty-state title="No comments yet" :heading="$emptyHeading" />
@endif
