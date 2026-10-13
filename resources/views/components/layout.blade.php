@props([
    'js' => [], // page scripts, e.g. ['resources/js/deck-builder.js']
    'css' => [], // page styles, e.g. ['resources/css/page.css']
    'class' => '',
    'hideNav' => false,
    'title' => null,
    'gameSwitch' => true, // false when the page places the game-switch component itself
])

{{-- $navSeries and $routeSeries come from the layout's view composer (AppServiceProvider). --}}

<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? "$title · DeckTrove" : 'DeckTrove' }}</title>

    {{-- Mark that JS runs (CSS keeps collapsed content visible without it), and apply the saved theme before the
    page paints, so dark mode doesn't flash white. --}}
    <script>
        (() => {
            document.documentElement.classList.add("js");
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

<body class="flex min-h-full flex-col bg-canvas font-sans text-ink antialiased {{ $class }}">
    <a href="#content"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-accent focus:px-4 focus:py-3 focus:font-bold focus:text-white">Skip to content</a>

    @unless($hideNav)
    <x-site-header :series="$navSeries" />
    @else
    <x-theme-toggle class="fixed right-4 top-4 z-50 bg-surface shadow-lg" />
    @endunless

    <main id="content" class="flex-1 {{ $class }}">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @if ($gameSwitch && ! $hideNav && $routeSeries)
            <div class="mb-6 flex justify-end">
                <x-game-switch :series="$routeSeries" />
            </div>
            @endif
            <x-flash />
            {{ $slot }}
        </div>
    </main>

    @unless($hideNav)
    <x-site-footer />
    @endunless

    <x-confirm-dialog />
</body>

</html>
