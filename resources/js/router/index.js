import { createRouter, createWebHistory } from 'vue-router'

import HomeLayout  from '@/Layouts/VirtualTourLayout/VTHomeLayout.vue'
import BelisLayout from '@/Layouts/VirtualTourLayout/BelisLayout.vue'

// ── Safe server-side redirect ─────────────────────────────────────────────────
// Only redirects when Vue Router is navigating client-side to a Laravel route.
// Guards against:
//   1. Infinite loop — skips redirect if browser is already on that path
//   2. Open redirect — validates destination stays on same origin
const safeServerRedirect = (to) => {
    const targetPath = to.fullPath

    // ── Loop prevention ───────────────────────────────────────────────────────
    // If the browser's current URL is already this path, the page was loaded
    // by Laravel (full server request). Don't redirect again — that causes
    // the infinite loop seen in the server logs.
    if (window.location.pathname === to.path) {
        return false  // let Vue Router render nothing; Laravel already served the page
    }

    // ── Open redirect prevention ──────────────────────────────────────────────
    if (!targetPath.startsWith('/') || targetPath.startsWith('//') || targetPath.startsWith('/\\')) {
        return { path: '/' }
    }

    const target = new URL(targetPath, window.location.origin)
    if (target.origin !== window.location.origin) {
        return { path: '/' }
    }

    // ── Safe: trigger a real browser navigation to let Laravel handle it ──────
    window.location.href = target.pathname + target.search + target.hash
}

const routes = [
    // ── Virtual Tour — Vue Router owns these completely ───────────────────────
    {
        path: '/VTHome',
        name: 'home',
        component: HomeLayout,
    },
    {
        path: '/location/:id',
        component: BelisLayout,
        children: [
            {
                path: '',
                name: 'location-view',
                component: () => import('@/Pages/VirtualTour/LocationView.vue'),
            },
        ],
    },

    // ── All other paths → hand off to Laravel/Inertia ─────────────────────────
    // The loop-prevention check in safeServerRedirect ensures this only fires
    // on client-side navigation attempts, not on initial full-page loads.
    {
        path: '/:pathMatch(.*)*',
        beforeEnter: safeServerRedirect,
        component: { template: '<div></div>' },
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router