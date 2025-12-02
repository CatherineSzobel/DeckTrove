@props([
'js' => [], // default empty array
'css' => [], // default empty array
])

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeckTrove</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @php
    $defaultAssets = [
    'resources/css/layout.css',
    'resources/js/layout.js',
    'resources/js/card-database-core.js',
    'resources/js/card-database.js',
    ];

    // Merge default assets with additional assets
    $allAssets = array_merge($defaultAssets, $css, $js);
    @endphp

    @vite($allAssets)

    <link rel="shortcut icon" href="{{ Vite::asset('resources/img/decktrove-logo.png') }}" />



</head>

<body class="h-full">
    <header>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div id="showcase" class="flex h-8 items-center justify-between">
                <div class="w-full flex flex-col items-center justify-center">
                    <div class="shrink-0">

                        <a href="#" id="showcase_button" class="flex flex-col items-center justify-center text-center w-full">
                            <p class="text-sm font-semibold cursor-pointer hover:text-blue-500 transition-colors text-center w-full">
                                dit is een showcase project.
                            </p>
                        </a>
                        <!-- Hidden section (appears in a row) -->
                        <section
                            id="showcaseDetails"
                            class="hidden w-full flex flex-col items-center justify-center gap-4 mt-4 transition-all duration-300">
                            <div class="h-1 w-24 bg-gray-300">
                                <div id="showcaseProgress" class="h-1 bg-blue-500"></div>
                            </div>
                            <p class="text-center">gemaakt met: Laravel, Tailwind CSS, JavaScript, PHP, HTML, MySQL</p>
                            <div id="container_img" class="flex flex-row space-x-2 justify-center">
                                <div class="flex flex-col items-center">
                                    <p>Frontend:</p>
                                    <img
                                        src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500"
                                        alt="Tailwind CSS Logo"
                                        class="h-6 w-6 inline-block" />
                                    <img
                                        src="https://upload.wikimedia.org/wikipedia/commons/6/61/HTML5_logo_and_wordmark.svg"
                                        alt="HTML5 Logo"
                                        class="h-6 w-6 inline-block" />
                                </div>
                                <div class="flex flex-col items-center">
                                    <p>Backend:</p>

                                    <img
                                        src="https://upload.wikimedia.org/wikipedia/commons/2/27/PHP-logo.svg"
                                        alt="PHP Logo"
                                        class="h-6 w-6 inline-block" />
                                    <img
                                        src="https://laravel.com/img/logomark.min.svg"
                                        alt="Laravel Logo"
                                        class="h-6 w-6 inline-block" />
                                </div>
                                <div class="flex flex-col items-center">
                                    <p>Database:</p>
                                    <img
                                        src="https://www.mysql.com/common/logos/logo-mysql-170x115.png"
                                        alt="MySQL Logo"
                                        class="h-6 w-6 inline-block" />
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="min-h-full">
        <nav class="bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo - Will be centered when on homepage -->
                    <div class="flex items-center @if(request()->is('/') || request()->is('*/card/*')) flex-1 justify-center @endif">
                        <div class="shrink-0">
                            <a href="/"><img src="{{ Vite::asset('resources/img/decktrove-logo-white.png') }}" alt="Logo" class="h-20 w-20 mt-2" /></a>
                        </div>

                        <!-- Desktop Menu - Hidden on homepage -->
                        @if (!request()->is('/') && !request()->is('*/card/*'))
                        <div class="hidden md:block ml-10">
                            <div class="flex items-baseline space-x-4">
                                @if (!request()->routeIs('register', 'login','profile', 'decks'))
                                <x-select-dropdown :class="'series-selector'" :options="['yugioh' => 'Yu-Gi-Oh!', 'magic' => 'Magic: The Gathering']">
                                </x-select-dropdown>
                                @endif

                                <x-nav-link id="home" href="/" :active="request()->is('home')" class="dashboard-link">Home</x-nav-link>

                                <!-- Cards Dropdown -->
                                <div class="relative group inline-block text-left">
                                    <x-dropdown-button>Cards</x-dropdown-button>

                                    <!-- Dropdown Menu -->
                                    <x-dropdown-menu>
                                        <x-dropdown-nav-link href="/cards" class="dashboard-link">Cards database</x-dropdown-nav-link>
                                        <x-dropdown-nav-link href="/packs" class="dashboard-link">Packs</x-dropdown-nav-link>
                                    </x-dropdown-menu>
                                </div>

                                <div class="relative group inline-block text-left">
                                    <x-dropdown-button>Decks</x-dropdown-button>

                                    <!-- Dropdown Menu -->
                                    <x-dropdown-menu>
                                        <x-dropdown-nav-link href="/public-deck" class="dashboard-link">Public decks</x-dropdown-nav-link>
                                        <x-dropdown-nav-link href="/deck-builder" class="dashboard-link">Deck builder</x-dropdown-nav-link>
                                    </x-dropdown-menu>
                                </div>

                                <!-- Other nav links -->
                                <x-nav-link href="/portfolio" :active="request()->is('about_me')">About me</x-nav-link>

                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Auth Links - Hidden on homepage -->
                    @if (!request()->is('/') )
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
                            @guest
                            <x-nav-link href="/register" :active="request()->is('register')">Register</x-nav-link>
                            <x-nav-link href="/login" :active="request()->is('login')">Login</x-nav-link>
                            @endguest
                            @auth
                            <form method="POST" action="/logout" id="logoutForm" class="hidden">
                                @csrf
                            </form>

                            <x-user-dropdown />
                            @endauth

                        </div>
                    </div>
                    @endif

                    <!-- Mobile menu button - Hidden on homepage -->
                    @if (!request()->is('/'))
                    <div class="-mr-2 flex md:hidden">
                        <button type="button" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-white/5 hover:text-white focus:outline-none" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Mobile menu - Only show if not on homepage -->
            @if (!request()->is('/'))
            <div class="md:hidden" id="mobile-menu">
                <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
                    <!-- Cards Dropdown for Mobile -->
                    <div class="space-y-1">
                        <button type="button" class="w-full flex justify-between items-center px-3 py-2 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-700">
                            Cards
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="pl-4 mt-1 space-y-1">
                            <a href="#" class="block px-3 py-2 text-sm text-gray-300 rounded-md hover:bg-gray-700 hover:text-white">Cards database</a>
                            <a href="#" class="block px-3 py-2 text-sm text-gray-300 rounded-md hover:bg-gray-700 hover:text-white">Packs</a>
                        </div>
                    </div>

                    <!-- Other nav links -->
                    <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Home</a>
                    <a href="/jobs" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Jobs</a>
                    <a href="/contact" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Contact</a>
                </div>

                <!-- Auth links -->
                <div class="border-t border-gray-700 pt-4 pb-3 px-2 space-y-1">
                    @guest
                    <a href="/login" class="block px-3 py-2 text-base font-medium text-gray-300 rounded-md hover:bg-gray-700 hover:text-white">Login</a>
                    <a href="/register" class="block px-3 py-2 text-base font-medium text-gray-300 rounded-md hover:bg-gray-700 hover:text-white">Register</a>
                    @endguest
                    @auth
                    <el-dropdown class="relative ml-3">
                        <button class="relative flex max-w-xs items-center rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">Open user menu</span>
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" class="size-8 rounded-full outline -outline-offset-1 outline-white/10" />
                        </button>

                        <el-menu anchor="bottom end" popover class="w-48 origin-top-right rounded-md bg-white py-1 shadow-lg outline-1 outline-black/5 transition transition-discrete [--anchor-gap:--spacing(2)] data-closed:scale-95 data-closed:transform data-closed:opacity-0 data-enter:duration-100 data-enter:ease-out data-leave:duration-75 data-leave:ease-in">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 focus:bg-gray-100 focus:outline-hidden">Your profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 focus:bg-gray-100 focus:outline-hidden">Settings</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 focus:bg-gray-100 focus:outline-hidden">Sign out</a>
                        </el-menu>
                    </el-dropdown>

                    @endauth
                </div>
            </div>
            @endif
        </nav>

        <main>
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
    <x-footer>
    </x-footer>
</body>

</html>