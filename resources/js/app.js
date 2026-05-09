import { createApp } from 'vue';
import { createPinia } from 'pinia';
import Root from './components/Root.vue';
import router from './router';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import 'select2/dist/css/select2.min.css';
import 'simple-line-icons/css/simple-line-icons.css';

function bootstrap() {
    const mountTarget = document.querySelector('#app');

    if (!mountTarget) {
        console.error('Vue mount target "#app" was not found.');
        return;
    }

    const app = createApp(Root);

    app.use(createPinia());
    app.use(router);
    app.use(PrimeVue, {
        theme: {
            preset: Aura,
            options: {
                darkModeSelector: '[data-theme="dark"]',
                cssLayer: false
            }
        }
    });

    app.mount(mountTarget);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap, { once: true });
} else {
    bootstrap();
}
