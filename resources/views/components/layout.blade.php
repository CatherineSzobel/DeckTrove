@props([
'js' => [], // example: ['resources/js/app.js']
'css' => [], // example: ['resources/css/app.css']
'class' => '',
'hideNav' => false
])

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeckTrove</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    @php
    $defaultAssets = [
    'resources/css/layout.scss',
    'resources/js/layout.js'
    ];

    $allAssets = array_merge($defaultAssets, $css, $js);
    @endphp
    @vite($allAssets)

    <link rel="shortcut icon" href="{{ Vite::asset('resources/img/decktrove-logo.png') }}" />
</head>

<body class="h-full">

    @unless($hideNav)
    <!-- HEADER / SHOWCASE -->
    <header>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div id="showcase" class="flex flex-col items-center justify-start w-full">
                <a href="#" id="showcase_button" class="flex flex-col items-center justify-center text-center w-full">
                    <p class="text-sm font-semibold cursor-pointer hover:text-blue-500 transition-colors w-full text-center">
                        This is a showcase project.
                    </p>
                </a>

                <section id="showcaseDetails" class="max-h-0 overflow-hidden w-full flex flex-col items-center gap-4 mt-4 transition-all duration-500 ease-in-out">
                    <div class="h-1 w-24 bg-gray-300 relative">
                        <div id="showcaseProgress" class="h-1 bg-blue-500 transition-all duration-500 ease-in-out"></div>
                    </div>

                    <p class="text-center">Made with: HTML, Tailwind CSS, JavaScript, SASS, PHP, Laravel, MySQL</p>

                    <div id="container_img" class="flex flex-row flex-wrap justify-center gap-6">
                        <!-- Frontend -->
                        <div class="flex flex-col items-center">
                            <p>Frontend:</p>
                            <img src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" alt="Tailwind CSS Logo" class="h-6 w-6" />
                            <img src="https://upload.wikimedia.org/wikipedia/commons/6/61/HTML5_logo_and_wordmark.svg" alt="HTML5 Logo" class="h-6 w-6" />
                            <img src="https://upload.wikimedia.org/wikipedia/commons/d/d5/CSS3_logo_and_wordmark.svg" alt="CSS3 Logo" class="h-6 w-6" />
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Unofficial_JavaScript_logo_2.svg" alt="JavaScript Logo" class="h-6 w-6" />
                        </div>
                        <!-- Backend -->
                        <div class="flex flex-col items-center">
                            <p>Backend:</p>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/2/27/PHP-logo.svg" alt="PHP Logo" class="h-6 w-6" />
                            <img src="https://laravel.com/img/logomark.min.svg" alt="Laravel Logo" class="h-6 w-6" />
                        </div>
                        <!-- Database -->
                        <div class="flex flex-col items-center">
                            <p>Database:</p>
                            <img src="https://www.mysql.com/common/logos/logo-mysql-170x115.png" alt="MySQL Logo" class="h-6 w-6" />
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </header>

    <!-- NAVBAR -->
    <nav class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="shrink-0">
                        <a href="/"><img src="{{ Vite::asset('resources/img/decktrove-logo-white.png') }}" alt="Logo" class="h-20 w-20 mt-2" /></a>
                    </div>

                    @if (!request()->is('/'))
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">

                            @if (!request()->routeIs('register','login','profile','decks','public-deck') && !request()->is('*/card/*') && !request()->is('*/pack/*'))
                            <x-select-dropdown :class="'series-selector'" :options="['yugioh' => 'Yu-Gi-Oh!', 'magic' => 'Magic: The Gathering']" />
                            @endif

                            @auth
                            <x-nav-link href="/dashboard" :active="request()->is('home')" class="dashboard-link">Home</x-nav-link>
                            @endauth

                            <!-- Cards Dropdown -->
                            <div class="relative group inline-block text-left">
                                <x-dropdown-button>Cards</x-dropdown-button>
                                <x-dropdown-menu>
                                    <x-dropdown-nav-link href="/cards" class="dashboard-link">Cards database</x-dropdown-nav-link>
                                    <x-dropdown-nav-link href="/packs" class="dashboard-link">Packs</x-dropdown-nav-link>
                                </x-dropdown-menu>
                            </div>

                            <!-- Decks Dropdown -->
                            <div class="relative group inline-block text-left">
                                <x-dropdown-button>Decks</x-dropdown-button>
                                <x-dropdown-menu>
                                    <x-dropdown-nav-link href="/public-deck">Public decks</x-dropdown-nav-link>
                                    <x-dropdown-nav-link href="/deck-builder" class="dashboard-link">Deck builder</x-dropdown-nav-link>
                                </x-dropdown-menu>
                            </div>

                            <x-nav-link href="/portfolio" :active="request()->is('about_me')">About me</x-nav-link>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Auth Links -->
                @if (!request()->is('/'))
                <div class="hidden md:block ml-4 md:ml-6">
                    @guest
                    <x-nav-link href="/register" :active="request()->is('register')">Register</x-nav-link>
                    <x-nav-link href="/login" :active="request()->is('login')">Login</x-nav-link>
                    @endguest
                    @auth
                    <form method="POST" action="/logout" id="logoutForm" class="hidden">@csrf</form>
                    <x-user-dropdown />
                    @endauth
                </div>
                @endif
            </div>
        </div>
    </nav>
    @endunless

    <!-- MAIN CONTENT -->
    <main class="{{ $class }}">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </main>
    @unless($hideNav)
    <!-- FOOTER -->
    <x-footer />
    @endunless
</body>

</html>