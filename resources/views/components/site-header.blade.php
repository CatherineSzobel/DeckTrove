{{-- The site header: always dark (.dark-scope), the nav links from App\View\Components\SiteHeader, account controls. --}}

<header x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false" class="dark-scope border-b border-line bg-canvas text-ink">
    <nav aria-label="Main">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-3">
            <div class="flex items-center gap-7">
                <a href="{{ $homeUrl }}" class="focus-ring flex items-center gap-2.5 rounded-lg">
                    <img src="{{ Vite::asset('resources/img/decktrove-logo-white.png') }}" alt="" class="h-10 w-10 object-contain">
                    <span class="font-display text-2xl font-extrabold uppercase tracking-[0.04em]">DeckTrove</span>
                </a>

                <div class="hidden items-center gap-6 text-[15px] font-medium md:flex">
                    @foreach ($links as $link)
                    <a href="{{ $link['href'] }}" data-nav="{{ $link['key'] }}" @if ($link['active']) aria-current="page" @endif
                        @class([
                            'focus-ring inline-flex min-h-11 items-center rounded-sm transition-colors',
                            'text-ink underline decoration-accent-ink decoration-2 underline-offset-8' => $link['active'],
                            'text-body hover:text-ink' => ! $link['active'],
                        ])>{{ $link['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-theme-toggle />

                <div class="hidden items-center gap-3 md:flex">
                    @auth
                    <x-user-menu :user="auth()->user()" />
                    @else
                    <a href="{{ route('login') }}" @class(['focus-ring inline-flex min-h-11 items-center rounded-sm px-2 text-[15px] font-medium', 'text-ink' => request()->routeIs('login'), 'text-body hover:text-ink' => ! request()->routeIs('login')])>Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary h-11 px-4 text-[15px]">Create account</a>
                    @endauth
                </div>

                <button type="button" @click="menuOpen = ! menuOpen" :aria-expanded="menuOpen.toString()" aria-expanded="false"
                    aria-controls="mobileMenu" aria-label="Open main menu"
                    class="focus-ring inline-flex h-11 w-11 items-center justify-center rounded-[10px] border border-line text-body hover:text-ink md:hidden">
                    <x-heroicon-o-bars-3 class="h-6 w-6" />
                </button>
            </div>
        </div>

        <div id="mobileMenu" x-cloak x-show="menuOpen" class="border-t border-line px-6 pb-4 pt-2 md:hidden">
            <div class="flex flex-col gap-1">
                @foreach ($links as $link)
                <a href="{{ $link['href'] }}" @if ($link['active']) aria-current="page" @endif
                    @class([
                        'focus-ring rounded-lg px-3 py-3 text-[15px] font-medium',
                        'bg-surface-2 text-ink' => $link['active'],
                        'text-body hover:text-ink' => ! $link['active'],
                    ])>{{ $link['label'] }}</a>
                @endforeach
            </div>

            <div class="mt-2 flex flex-col gap-1 border-t border-line pt-2">
                @auth
                <a href="{{ route('dashboard') }}" class="focus-ring rounded-lg px-3 py-3 text-body hover:text-ink">Dashboard</a>
                <a href="{{ route('decks.mine') }}" class="focus-ring rounded-lg px-3 py-3 text-body hover:text-ink">My decks</a>
                <a href="{{ route('profile') }}" class="focus-ring rounded-lg px-3 py-3 text-body hover:text-ink">Profile</a>
                <a href="{{ route('settings') }}" class="focus-ring rounded-lg px-3 py-3 text-body hover:text-ink">Settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="focus-ring w-full rounded-lg px-3 py-3 text-left text-body hover:text-ink">Log out</button>
                </form>
                @else
                <a href="{{ route('login') }}" class="focus-ring rounded-lg px-3 py-3 text-body hover:text-ink">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary mt-1 h-11">Create account</a>
                @endauth
            </div>
        </div>
    </nav>
</header>
