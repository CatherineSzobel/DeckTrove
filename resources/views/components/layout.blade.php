@props([
    'js' => [], // page scripts, e.g. ['resources/js/deck.js']
    'css' => [], // page styles, e.g. ['resources/css/deckbuilder.css']
    'class' => '',
    'hideNav' => false,
    'title' => null,
])

@php
    $seriesLabels = collect(config('series'))->map(fn ($config) => $config['label'])->all();
    $comingSoon = collect(config('coming_soon'))->map(fn ($config) => $config['label'])->all();
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? "$title · DeckTrove" : 'DeckTrove' }}</title>

    {{-- Apply the saved theme before the page paints, so dark mode doesn't flash white. --}}
    <script>
        (() => {
            let theme = null;
            try { theme = localStorage.getItem("theme"); } catch {}
            if (theme === "dark" || (!theme && matchMedia("(prefers-color-scheme: dark)").matches)) {
                document.documentElement.classList.add("dark");
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/layout.js', ...$css, ...$js])

    <link rel="icon" href="{{ Vite::asset('resources/img/decktrove-logo.png') }}" />
</head>

<body class="h-full text-gray-900 {{ $class }}">

    @unless($hideNav)
    <header>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div id="showcase" class="flex flex-col items-center justify-start w-full">
                <button type="button" id="showcase_button" aria-expanded="false" aria-controls="showcaseDetails"
                    class="text-sm font-semibold hover:text-blue-500 transition-colors w-full text-center py-1">
                    This is a showcase project.
                </button>

                <section id="showcaseDetails" class="max-h-0 opacity-0 overflow-hidden w-full transition-all duration-500 ease-in-out">
                    <p class="text-center">Made with: HTML, Tailwind CSS, JavaScript, PHP, Laravel, MySQL</p>

                    <div class="flex flex-row flex-wrap justify-center gap-6 py-2">
                        <div class="flex flex-col items-center">
                            <p>Frontend:</p>
                            <img src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" alt="Tailwind CSS" class="h-6 w-6" />
                            <img src="https://upload.wikimedia.org/wikipedia/commons/6/61/HTML5_logo_and_wordmark.svg" alt="HTML5" class="h-6 w-6" />
                            <img src="https://upload.wikimedia.org/wikipedia/commons/d/d5/CSS3_logo_and_wordmark.svg" alt="CSS3" class="h-6 w-6" />
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Unofficial_JavaScript_logo_2.svg" alt="JavaScript" class="h-6 w-6" />
                        </div>
                        <div class="flex flex-col items-center">
                            <p>Backend:</p>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/2/27/PHP-logo.svg" alt="PHP" class="h-6 w-6" />
                            <img src="https://laravel.com/img/logomark.min.svg" alt="Laravel" class="h-6 w-6" />
                        </div>
                        <div class="flex flex-col items-center">
                            <p>Database:</p>
                            <img src="https://www.mysql.com/common/logos/logo-mysql-170x115.png" alt="MySQL" class="h-6 w-6" />
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </header>

    <nav class="bg-slate-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('index') }}" class="shrink-0">
                        <img src="{{ Vite::asset('resources/img/decktrove-logo-white.png') }}" alt="DeckTrove home" class="h-20 w-20 mt-2" />
                    </a>

                    <div class="hidden md:flex items-center ml-10 space-x-4">
                        <x-nav-links :series="$navSeries" :series-labels="$seriesLabels" :coming-soon="$comingSoon" />
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-2 ml-4 md:ml-6">
                    <x-theme-toggle />
                    @guest
                    <x-nav-link :href="route('register')" :active="request()->routeIs('register')">Register</x-nav-link>
                    <x-nav-link :href="route('login')" :active="request()->routeIs('login')">Login</x-nav-link>
                    @endguest
                    @auth
                    <x-user-dropdown />
                    @endauth
                </div>

                <div class="flex items-center gap-1 md:hidden">
                    <x-theme-toggle />
                    <button type="button" id="mobileMenuButton" aria-controls="mobileMenu" aria-expanded="false"
                        class="inline-flex items-center justify-center rounded-md p-2 text-slate-300 hover:bg-slate-700 hover:text-white">
                        <span class="sr-only">Open main menu</span>
                        <x-heroicon-o-bars-3 class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </div>

        <div id="mobileMenu" class="hidden md:hidden border-t border-slate-700 px-4 pb-4 pt-2 space-y-2">
            <x-nav-links :series="$navSeries" :series-labels="$seriesLabels" :coming-soon="$comingSoon" mobile />
            <div class="border-t border-slate-700 pt-2 flex flex-col gap-1">
                @guest
                <x-nav-link :href="route('register')" :active="request()->routeIs('register')">Register</x-nav-link>
                <x-nav-link :href="route('login')" :active="request()->routeIs('login')">Login</x-nav-link>
                @endguest
                @auth
                <x-nav-link :href="route('profile')">Profile</x-nav-link>
                <x-nav-link :href="route('decks')">My decks</x-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left rounded-md px-3 py-2 text-sm font-medium text-slate-300 hover:bg-white/5 hover:text-white">Logout</button>
                </form>
                @endauth
            </div>
        </div>
    </nav>
    @else
    <x-theme-toggle class="fixed top-4 right-4 z-50 bg-slate-800 shadow-lg" />
    @endunless

    <main class="{{ $class }}">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <x-flash />
            {{ $slot }}
        </div>
    </main>

    @unless($hideNav)
    <x-footer />
    @endunless
</body>

</html>
