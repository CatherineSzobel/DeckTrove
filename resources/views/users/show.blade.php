{{--
    A user's public profile: who they are, what they shared and the likes it got. Never the email, never private decks
    (also not for the owner, who edits their account at /profile). Sort and series are plain links.
--}}
@php
    $current = array_filter(['sort' => $filters['sort'], 'game' => $filters['game']], fn ($value) => $value !== null);
    $url = fn (array $changes) => route('users.show', [$user, ...array_filter(array_merge($current, $changes), fn ($value) => $value !== null)]);
    $segment = fn (bool $active) => $active ? 'bg-surface-2 font-bold text-ink' : 'font-medium text-muted hover:text-ink';
    $avatar = $user->avatarUrl();
@endphp

<x-layout :title="$user->username">
    <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-sm text-muted">
            <a href="{{ route('public-deck') }}" class="focus-ring text-body hover:text-ink">Public decks</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-ink">{{ $user->username }}</span>
        </nav>

        <div class="flex flex-wrap items-start gap-6">
            <section aria-label="Profile" class="min-w-0 flex-[1_1_300px] overflow-hidden rounded-3xl border border-line bg-surface lg:max-w-[400px]">
                <div class="relative h-[120px] overflow-hidden border-b border-line bg-canvas" aria-hidden="true">
                    <div class="absolute right-[86px] top-[34px] aspect-[59/86] w-16 -rotate-12 rounded-md bg-surface-2 shadow-lg"></div>
                    <div class="absolute right-6 top-[34px] aspect-[59/86] w-16 rotate-[10deg] rounded-md bg-surface-2 shadow-lg"></div>
                    <div class="absolute right-[54px] top-[18px] aspect-[59/86] w-16 rounded-md border border-line-strong bg-canvas shadow-xl"></div>
                </div>

                <div class="flex flex-col gap-4 px-6 pb-6">
                    @if ($avatar)
                    <img src="{{ $avatar }}" alt="" class="relative -mt-12 h-24 w-24 rounded-full border-4 border-surface object-cover">
                    @else
                    <span aria-hidden="true" class="relative -mt-12 flex h-24 w-24 items-center justify-center rounded-full border-4 border-surface bg-accent font-display text-[42px] font-extrabold text-white">{{ Str::upper(Str::substr($user->username, 0, 1)) }}</span>
                    @endif

                    <div class="flex flex-col gap-2">
                        <p class="eyebrow">Player · joined {{ $user->created_at->format('F Y') }}</p>
                        <h1 class="heading-display break-words text-[44px] text-ink">{{ $user->username }}</h1>
                    </div>

                    @if ($user->about)
                    <p class="text-[15px] leading-relaxed text-body">{{ $user->about }}</p>
                    @endif

                    <div class="flex flex-wrap gap-2.5">
                        <span class="inline-flex items-baseline gap-2 rounded-[10px] border border-line bg-canvas px-3 py-2 text-[13px] text-body">
                            <b class="font-mono text-base text-accent-ink">{{ $stats['public_decks'] }}</b>
                            public {{ Str::plural('deck', $stats['public_decks']) }}
                        </span>
                        <span class="inline-flex items-baseline gap-2 rounded-[10px] border border-line bg-canvas px-3 py-2 text-[13px] text-body">
                            <b class="font-mono text-base text-ink">{{ $stats['likes_received'] }}</b>
                            {{ Str::plural('like', $stats['likes_received']) }} received
                        </span>
                    </div>

                    @if ($isOwner)
                    <a href="{{ route('profile.edit') }}" class="btn-secondary">
                        <x-heroicon-o-pencil-square class="h-5 w-5" aria-hidden="true" />
                        Edit profile
                    </a>
                    <p class="-mt-1.5 text-center text-xs text-muted">This is how other players see your profile. Private decks never show here.</p>
                    @endif
                </div>
            </section>

            <section aria-labelledby="profile-decks" class="flex min-w-0 flex-[999_1_640px] flex-col gap-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div class="flex flex-col gap-2">
                        <p class="eyebrow">Shared by {{ $user->username }}</p>
                        <h2 id="profile-decks" class="heading-display text-[44px] text-ink">Public decks</h2>
                    </div>

                    @if ($stats['public_decks'] > 0)
                    <div class="flex flex-wrap gap-2.5">
                        <nav aria-label="Series" class="inline-flex gap-1 rounded-xl border border-line bg-surface p-1">
                            <a href="{{ $url(['game' => null]) }}" data-game="all" @if (! $filters['game']) aria-current="true" @endif
                                class="focus-ring flex min-h-10 items-center rounded-[9px] px-3.5 text-sm {{ $segment(! $filters['game']) }}">All</a>
                            @foreach (config('series') as $key => $config)
                            <a href="{{ $url(['game' => $key]) }}" data-game="{{ $key }}" @if ($filters['game'] === $key) aria-current="true" @endif
                                class="focus-ring flex min-h-10 items-center rounded-[9px] px-3.5 text-sm {{ $segment($filters['game'] === $key) }}">{{ $config['label'] }}</a>
                            @endforeach
                        </nav>
                        <nav aria-label="Sort decks" class="inline-flex gap-1 rounded-xl border border-line bg-surface p-1 max-sm:w-full">
                            @foreach ($sorts as $key => $label)
                            <a href="{{ $url(['sort' => $key]) }}" data-sort="{{ $key }}" @if ($filters['sort'] === $key) aria-current="true" @endif
                                class="focus-ring flex min-h-10 items-center justify-center whitespace-nowrap rounded-[9px] px-3.5 text-sm max-sm:flex-1 {{ $segment($filters['sort'] === $key) }}">{{ $label }}</a>
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
                    <x-slot:actions><a href="{{ $url(['game' => null]) }}" class="btn-secondary">Show all series</a></x-slot:actions>
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
