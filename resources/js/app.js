import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import router from './router/index.js'; // <--- 1. IMPORT your virtual tour router

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faGauge, faUserPlus, faUsers, faChartBar, faCog, faBell, faUser, faFilter } from '@fortawesome/free-solid-svg-icons';

library.add(faGauge, faUserPlus, faUsers, faChartBar, faCog, faBell, faUser, faFilter);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });

        vueApp
            .use(plugin)
            .use(ZiggyVue)
            .use(router) // <--- 2. ADD THIS LINE to activate the virtual tour routing
            .component('FontAwesomeIcon', FontAwesomeIcon)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});