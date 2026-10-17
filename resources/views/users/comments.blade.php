{{-- All comments on a profile (/u/{name}/comments), newest first, 20 per page, with the form. --}}
<x-layout :title="$page->title()" :js="['resources/js/profile-page.js']">
    <div class="mx-auto flex max-w-4xl flex-col gap-8 px-4 py-8 sm:px-6">
        @include('users.partials.breadcrumb')

        <div id="comments" class="flex scroll-mt-6 flex-col gap-6">
            <h1 class="heading-display text-[44px] text-ink">{{ $page->heading() }}</h1>
            <x-comments.box :can-post="$page->canComment()" :action="route('users.comments.store', $user)" :draft="$page->commentDraft()" />
            <x-comments.list :comments="$page->comments()" empty-heading="h2" />
            <x-pagination :paginator="$page->comments()" />
        </div>
    </div>
</x-layout>
