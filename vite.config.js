import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/layout.scss',
                'resources/css/deckbuilder.css',
                'resources/js/app.js',
                'resources/js/layout.js',
                'resources/js/card-database-core.js',
                'resources/js/card-database.js',
                'resources/js/deck-builder.js',
                'resources/js/tabs.js',
                'resources/js/portfolio.js',
                'resources/js/portfolio.scss',
                'resources/js/home.scss',
                'resources/js/card.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
