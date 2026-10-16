{{-- The top of a profile list page: Public decks / {name} (back to the profile) / this list. --}}
<nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-muted">
    <a href="{{ route('public-deck') }}" class="focus-ring text-body hover:text-ink">Public decks</a>
    <span aria-hidden="true">/</span>
    <a href="{{ route('users.show', $user) }}" class="focus-ring text-body hover:text-ink">{{ $user->username }}</a>
    <span aria-hidden="true">/</span>
    <span aria-current="page" class="text-ink">{{ $page->heading() }}</span>
</nav>
