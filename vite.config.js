import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    build: {
        // @ckeditor/ckeditor5-build-classic is ~1.3 MiB minified; splitting it out still leaves a chunk over 500 KiB.
        chunkSizeWarningLimit: 1536,
        rolldownOptions: {
            checks: {
                pluginTimings: false,
            },
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return;
                    }
                    if (id.includes('ckeditor')) {
                        return 'ckeditor';
                    }
                    if (id.includes('@primevue') || id.includes('/primevue/')) {
                        return 'primevue';
                    }
                    if (id.includes('quill')) {
                        return 'quill';
                    }
                    if (id.includes('jquery') || id.includes('select2')) {
                        return 'jquery';
                    }
                    if (id.includes('sweetalert2')) {
                        return 'sweetalert2';
                    }
                    if (
                        id.includes('vue-router') ||
                        /[/\\]node_modules[/\\]vue[/\\]/.test(id)
                    ) {
                        return 'vue-core';
                    }
                    if (id.includes('axios')) {
                        return 'axios';
                    }
                    return 'vendor';
                },
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
