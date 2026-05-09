import { createApp } from 'vue';
import { createPinia } from 'pinia';
import Root from './components/Root.vue';
import router from './router';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import 'select2/dist/css/select2.min.css';
import 'simple-line-icons/css/simple-line-icons.css';

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
app.mount('#app');
