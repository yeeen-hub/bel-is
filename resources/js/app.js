import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import vueRouter from './router/index.js'; // <--- virtual tour router

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faGauge, faUserPlus, faUsers, faChartBar, faCog, faBell, faUser, faFilter } from '@fortawesome/free-solid-svg-icons';

library.add(faGauge, faUserPlus, faUsers, faChartBar, faCog, faBell, faUser, faFilter);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// ── Session expiry handler ────────────────────────────────────────────────────
// ValidateSessionOwnership middleware returns HTTP 409 for Inertia requests
// when the session is no longer valid (tab closed, forced logout, etc.).
// Catch it here and do a full-page redirect to login to clear state cleanly.
router.on('invalid', (event) => {
    if (event.detail?.response?.status === 409) {
        event.preventDefault();
        window.location.href = '/login';
    }
});
// ─────────────────────────────────────────────────────────────────────────────

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
            .use(vueRouter) // <--- virtual tour routing
            .component('FontAwesomeIcon', FontAwesomeIcon)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});