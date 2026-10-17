{{--
    A user's public profile: the profile card with jump links, then Public decks, Liked decks and Comments, each a
    short list with a link to its full page. Never the email, never private decks (also not for the owner, who edits
    their profile at /profile/edit). App\Profiles\ProfilePage decides which sections show, their counts and lists.
--}}

<x-layout :title="$user->username" :js="['resources/js/profile-page.js']">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-muted">
            <a href="{{ route('public-deck') }}" class="focus-ring text-body hover:text-ink">Public decks</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-ink">{{ $user->username }}</span>
        </nav>

        <div class="flex flex-wrap items-start gap-8">
            <x-profile.card :user="$user" class="flex-[1_1_300px] lg:sticky lg:top-6 lg:max-h-[calc(100dvh-3rem)] lg:overflow-y-auto lg:max-w-[380px]">
                <div class="flex flex-col gap-2">
                    <p class="eyebrow">Player · joined {{ $user->created_at->format('F Y') }}</p>
                    <h1 class="heading-display break-words text-[44px] text-ink">{{ $user->username }}</h1>
                </div>

                @if ($user->about)
                <p class="text-[15px] leading-relaxed text-body">{{ $user->about }}</p>
                @endif

                <div class="flex flex-col border-t border-line pt-2">
                    <nav aria-label="On this page" class="flex flex-col">
                        @foreach ($page->sections() as $section)
                        <a href="#{{ $section['id'] }}" class="focus-ring flex min-h-11 items-center justify-between gap-3 rounded-sm text-[15px] text-body hover:text-ink">
                            {{ $section['label'] }}
                            <b class="font-mono text-ink">{{ $section['count'] }}</b>
                        </a>
                        @endforeach
                    </nav>
                    <p class="flex min-h-11 items-center justify-between gap-3 text-[15px] text-muted">
                        Likes received
                        <b class="font-mono text-ink">{{ $page->likesReceived() }}</b>
                    </p>
                </div>

                @if ($page->isOwner())
                <a href="{{ route('profile.edit') }}" class="btn-secondary">
                    <x-heroicon-o-pencil-square class="h-5 w-5" aria-hidden="true" />
                    Edit profile
                </a>
                <p class="-mt-1.5 text-center text-xs text-muted">This is how other players see your profile. Private decks never show here.</p>
                @endif
            </x-profile.card>

            <div class="flex min-w-0 flex-[999_1_640px] flex-col gap-14">
                <section id="decks" aria-labelledby="decks-title" class="flex scroll-mt-6 flex-col gap-5">
                    @include('users.partials.section-heading', ['id' => 'decks', 'title' => 'Public decks', 'more' => $page->moreLink('decks')])

                    @if ($page->overviewDecks()->isNotEmpty())
                    <div class="grid grid-cols-[repeat(auto-fill,minmax(min(260px,100%),1fr))] gap-5">
                        @foreach ($page->overviewDecks() as $deck)
                        <x-deck.card :deck="$deck" variant="profile" heading="h3" />
                        @endforeach
                    </div>
                    @elseif ($page->isOwner())
                    <x-empty-state title="You haven’t shared a deck yet" heading="h3">
                        Make a deck public from the deck builder and it shows up here for other players.
                        <x-slot:actions><a href="{{ route('decks') }}" class="btn-primary">Go to my decks</a></x-slot:actions>
                    </x-empty-state>
                    @else
                    <x-empty-state title="No public decks yet" heading="h3">{{ $user->username }} hasn’t shared any decks yet.</x-empty-state>
                    @endif
                </section>

                @if ($page->showsLiked())
                <section id="liked" aria-labelledby="liked-title" class="flex scroll-mt-6 flex-col gap-5">
                    @include('users.partials.section-heading', ['id' => 'liked', 'title' => 'Liked decks', 'more' => $page->moreLink('liked'), 'private' => $page->likedIsPrivate()])

                    @if ($page->overviewLiked()->isNotEmpty())
                    <ul class="grid grid-cols-[repeat(auto-fill,minmax(min(220px,100%),1fr))] gap-3">
                        @foreach ($page->overviewLiked() as $deck)
                        <x-deck.list-item :deck="$deck" />
                        @endforeach
                    </ul>
                    @elseif ($page->isOwner())
                    <x-empty-state title="You haven’t liked any decks yet" heading="h3">
                        Like a deck on Public decks and it shows up here.
                        <x-slot:actions><a href="{{ route('public-deck') }}" class="btn-primary">Browse public decks</a></x-slot:actions>
                    </x-empty-state>
                    @else
                    <x-empty-state title="No liked decks yet" heading="h3">{{ $user->username }} hasn’t liked any decks yet.</x-empty-state>
                    @endif
                </section>
                @endif

                @if ($page->showsComments())
                <section id="comments" aria-labelledby="comments-title" class="flex scroll-mt-6 flex-col gap-5">
                    @include('users.partials.section-heading', ['id' => 'comments', 'title' => 'Comments', 'more' => null])
                    @include('users.partials.comment-box')
                    <x-comments.list :comments="$page->overviewComments()" :owner-id="$user->id" owner-badge="Profile owner" />
                    @if ($page->moreLink('comments'))
                    <a href="{{ $page->moreLink('comments')['href'] }}" class="focus-ring flex min-h-11 items-center self-start rounded-sm font-bold text-accent-ink">{{ $page->moreLink('comments')['label'] }}</a>
                    @endif
                </section>
                @endif
            </div>
        </div>
    </div>
</x-layout>
