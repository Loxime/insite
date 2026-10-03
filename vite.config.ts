import { defineConfig } from 'vite';
import Symfony from '@symfony/reprise/vite';

export default defineConfig({
    input: {
        app: './assets/scripts/app.ts',
    },

    plugins: [
        Symfony({
            outputPath: 'public/build',
            publicPath: '/build/',
        }),
    ],
});
