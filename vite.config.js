import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/sass/app.scss', 'resources/js/app.js', 'resources/js/custom-dropdown.js', 'resources/js/mobile.js', 'resources/js/sweetalert-confirm.js', 'resources/js/confirmation-handlers.js'],
            refresh: true,
        }),
    ],
});
