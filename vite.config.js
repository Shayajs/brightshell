import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                // Entrée dédiée : évite ViteException si une vue utilise encore @vite(['resources/css/pages/services.css'])
                'resources/css/pages/services.css',
                'resources/js/portal-shell.js',
                'resources/js/collabs-team-messages.js',
                'resources/js/app.js',
                'resources/js/cv.js',
                'resources/js/realisations.js',
                'resources/js/services.js',
                'resources/js/auth-perlin.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
