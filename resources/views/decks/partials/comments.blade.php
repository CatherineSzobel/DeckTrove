{{-- The deck's comments ($comments: App\Decks\DeckComments): open to everyone, or read-only for the owner with a note. --}}
<section id="comments" aria-labelledby="comments-title" class="flex scroll-mt-6 flex-col gap-6">
    <h2 id="comments-title" class="heading-display flex items-baseline gap-3 text-[26px] text-ink">
        Comments
        <span class="font-mono text-base font-bold text-muted">{{ $comments->count() }}</span>
    </h2>

    @if ($comments->note())
    <p class="flex items-start gap-2 text-sm text-muted">
        <x-heroicon-o-lock-closed class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
        <span>
            {{ $comments->note() }}
            @if ($comments->showsEditLink())
            <a href="{{ route('decks.edit', $deck) }}" class="focus-ring font-bold text-accent-ink">Edit deck</a>
            @endif
        </span>
    </p>
    @endif

    @if ($comments->canPost())
    <x-comment-form :action="route('decks.comments.store', $deck)" :draft="$comments->draft()" />
    @elseif ($comments->isOpen())
    <p class="text-[15px] text-body"><a href="{{ route('login') }}" class="focus-ring font-bold text-accent-ink">Log in</a> to leave a comment.</p>
    @endif

    <x-comments.list :comments="$comments->comments()" :owner-id="$comments->ownerId()" owner-badge="Deck owner" />
    <x-pagination :paginator="$comments->comments()" />
</section>
