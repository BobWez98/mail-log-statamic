import statamic from '@statamic/cms/vite-plugin'
import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'

export default defineConfig({
    plugins: [
        statamic(),
        laravel({
            input: ['resources/js/cp.js'],
            publicDirectory: 'resources/dist',
        }),
    ],
})
