import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    build: {
        // ckeditor5 is a large bundle; splitting it out still leaves a chunk over 500 KiB.
        chunkSizeWarningLimit: 1536,
        rolldownOptions: {
            checks: {
                pluginTimings: false,
            },
            output: {
                manualChunks(id) {
                    // Keep RichTextEditor + classicEditor out of the shared UI
                    // chunk so every page does not eagerly pull CKEditor (~1MB).
                    if (
                        id.includes('resources/js/components/ui/RichTextEditor')
                        || id.includes('resources/js/lib/classicEditor')
                    ) {
                        return 'rich-text-editor';
                    }
                    if (
                        id.includes('resources/js/components/ui')
                        && !id.includes('RichTextEditor')
                    ) {
                        return 'ui';
                    }
                    if (
                        id.includes('resources/js/composables')
                        || id.includes('resources/js/utils')
                    ) {
                        return 'app-shared';
                    }
                    if (!id.includes('node_modules')) {
                        return;
                    }
                    // The resolved editor is the pre-bundled `ckeditor5/dist/browser`
                    // file (see resolve.alias). Keep it and its CSS/translations in one
                    // async chunk so login does not pull ~1.5MB. Do NOT also pull
                    // `@ckeditor/ckeditor5-*` packages: npm nests extra copies of
                    // ckeditor5-utils under those packages, and evaluating two copies
                    // throws `ckeditor-duplicated-modules`.
                    if (/[/\\]node_modules[/\\]ckeditor5[/\\]/.test(id)) {
                        return 'ckeditor';
                    }
                    if (id.includes('@primevue') || id.includes('/primevue/')) {
                        return 'primevue';
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
        dedupe: ['ckeditor5', 'vue', 'vue-router'],
        alias: [
            // `ckeditor5`'s package root is a barrel that re-exports every plugin
            // package. npm installs those plugins with nested copies of
            // `@ckeditor/ckeditor5-utils`, so the barrel evaluates the version
            // guard twice → `ckeditor-duplicated-modules`. The browser build is
            // a single pre-bundled module with one copy of the editor.
            {
                find: /^ckeditor5$/,
                replacement: path.resolve(__dirname, 'node_modules/ckeditor5/dist/browser/ckeditor5.js'),
            },
            {
                find: '@',
                replacement: path.resolve(__dirname, 'resources/js'),
            },
            // Do not alias @primeuix/themes into /dist — that breaks package "exports"
            // and leaves bare imports in the primevue chunk (blank SPA in the browser).
            {
                find: 'dompurify',
                replacement: path.resolve(__dirname, 'resources/js/utils/dompurify-lite.js'),
            },
            {
                find: 'pinia',
                replacement: path.resolve(__dirname, 'resources/js/utils/pinia-lite.js'),
            },
            {
                find: 'vue',
                replacement: 'vue/dist/vue.esm-bundler.js',
            },
        ],
    },
    optimizeDeps: {
        include: ['ckeditor5'],
    },
    define: {
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true,
        },
    },
});
