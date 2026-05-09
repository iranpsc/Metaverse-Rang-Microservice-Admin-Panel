import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    build: {
        // Main bundle is large until Rolldown-safe code-splitting is tuned (see comment below).
        chunkSizeWarningLimit: 3072,
        // Avoid custom manualChunks with Rolldown: assigning a large `ckeditor` chunk caused
        // Vue's runtime to be emitted inside that chunk while `vue-router` stayed separate.
        // The app then mixed two Vue module graphs → CKEditor mount crashed with
        // "Cannot read properties of null (reading 'nextSibling')" on production (e.g. login).
        rolldownOptions: {
            checks: {
                pluginTimings: false,
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        dedupe: ['vue', 'vue-router'],
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            dompurify: path.resolve(__dirname, 'resources/js/utils/dompurify-lite.js'),
            pinia: path.resolve(__dirname, 'resources/js/utils/pinia-lite.js'),
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    define: {
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
    },
});
