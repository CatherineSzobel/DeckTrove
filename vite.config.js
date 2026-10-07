import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // Every file passed to <x-layout :js/:css> must be listed here for production builds.
            input: [
                'resources/css/app.css',
                'resources/css/dashboard.css',
                'resources/css/deckbuilder.css',
                'resources/js/layout.js',
                'resources/js/account.js',
                'resources/js/card.js',
                'resources/js/card-database.js',
                'resources/js/carousel.js',
                'resources/js/deck.js',
                'resources/js/deck-builder.js',
                'resources/js/deckfilter.js',
                'resources/js/moving-carousel.js',
                'resources/js/mydecks.js',
                'resources/js/pack.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
