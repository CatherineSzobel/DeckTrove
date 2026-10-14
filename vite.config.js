import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // Every file passed to <x-layout :js/:css> must be listed here for production builds.
            input: [
                'resources/css/app.css',
                'resources/js/layout.js',
                'resources/js/account.js',
                'resources/js/card-database.js',
                'resources/js/card-page.js',
                'resources/js/carousel.js',
                'resources/js/deck-builder.js',
                'resources/js/deck-page.js',
                'resources/js/pack-page.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // Its own port, so it can run next to job-tracker (which needs 5173)
    server: {
        port: 5180,
        strictPort: true,
    },
});
