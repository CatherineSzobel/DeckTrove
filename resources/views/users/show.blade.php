{{--
    A user's public profile: who they are, what they shared and the likes it got. Never the email, never private decks
    (also not for the owner, who edits their account at /profile). Sort and series are plain links.
--}}

<x-layout :title="$user->username">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-muted">
            <a href="{{ route('public-deck') }}" class="focus-ring text-body hover:text-ink">Public decks</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-ink">{{ $user->username }}</span>
        </nav>

        <div class="flex flex-wrap items-start gap-6">
            <x-profile.card :user="$user" class="flex-[1_1_300px] lg:max-w-[400px]">
                <div class="flex flex-col gap-2">
                    <p class="eyebrow">Player · joined {{ $user->created_at->format('F Y') }}</p>
                    <h1 class="heading-display break-words text-[44px] text-ink">{{ $user->username }}</h1>
                </div>

                @if ($user->about)
                <p class="text-[15px] leading-relaxed text-body">{{ $user->about }}</p>
                @endif

                <div class="flex flex-wrap gap-2.5">
                    <x-stat-chip :value="$stats['public_decks']" :label="'public '.Str::plural('deck', $stats['public_decks'])" accent inset />
                    <x-stat-chip :value="$stats['likes_received']" :label="Str::plural('like', $stats['likes_received']).' received'" inset />
                </div>

                @if ($isOwner)
                <a href="{{ route('profile.edit') }}" class="btn-secondary">
                    <x-heroicon-o-pencil-square class="h-5 w-5" aria-hidden="true" />
                    Edit profile
                </a>
                <p class="-mt-1.5 text-center text-xs text-muted">This is how other players see your profile. Private decks never show here.</p>
                @endif
            </x-profile.card>

            <section aria-labelledby="profile-decks" class="flex min-w-0 flex-[999_1_640px] flex-col gap-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div class="flex flex-col gap-2">
                        <p class="eyebrow">Shared by {{ $user->username }}</p>
                        <h2 id="profile-decks" class="heading-display text-[44px] text-ink">Public decks</h2>
                    </div>

                    @if ($stats['public_decks'] > 0)
                    <div class="flex flex-wrap gap-2.5">
                        <nav aria-label="Series" class="inline-flex gap-1 rounded-xl border border-line bg-surface p-1">
                            <a href="{{ $query->url(['game' => null]) }}" data-game="all" @if (! $filters['game']) aria-current="true" @endif class="segment">All</a>
                            @foreach (config('series') as $key => $config)
                            <a href="{{ $query->url(['game' => $key]) }}" data-game="{{ $key }}" @if ($filters['game'] === $key) aria-current="true" @endif class="segment">{{ $config['label'] }}</a>
                            @endforeach
                        </nav>
                        <nav aria-label="Sort decks" class="inline-flex gap-1 rounded-xl border border-line bg-surface p-1 max-sm:w-full">
                            @foreach ($sorts as $key => $label)
                            <a href="{{ $query->url(['sort' => $key]) }}" data-sort="{{ $key }}" @if ($filters['sort'] === $key) aria-current="true" @endif
                                class="segment max-sm:flex-1">{{ $label }}</a>
                            @endforeach
                        </nav>
                    </div>
                    @endif
                </div>

                @if ($decks->isNotEmpty())
                @include('decks.partials.deck-card', [
                    'variant' => 'profile',
                    'heading' => 'h3',
                    'trending' => $filters['sort'] === 'trending',
                    'decksPath' => route('users.show', $user),
                ])
                @elseif ($stats['public_decks'] > 0)
                <x-empty-state title="No decks in this series" heading="h3">
                    <x-slot:actions><a href="{{ $query->url(['game' => null]) }}" class="btn-secondary">Show all series</a></x-slot:actions>
                </x-empty-state>
                @elseif ($isOwner)
                <x-empty-state title="You haven’t shared a deck yet" heading="h3">
                    Make a deck public from the deck builder and it shows up here for other players.
                    <x-slot:actions><a href="{{ route('decks') }}" class="btn-primary">Go to my decks</a></x-slot:actions>
                </x-empty-state>
                @else
                <x-empty-state title="No public decks yet" heading="h3">{{ $user->username }} hasn’t shared any decks yet.</x-empty-state>
                @endif
            </section>
        </div>
    </div>
</x-layout>
