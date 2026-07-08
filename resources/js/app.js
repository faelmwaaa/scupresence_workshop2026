import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import FlashToast from '@/Components/FlashToast.vue';

const appName = import.meta.env.VITE_APP_NAME || 'SCUPresence';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Register FlashToast globally so it's available everywhere
        vueApp.component('FlashToast', FlashToast);

        return vueApp.mount(el);
    },
    progress: {
        color: '#5B2163',
    },
});
