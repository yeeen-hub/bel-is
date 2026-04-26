<template>
    <LandingLayout>
        <!-- Header Section (UI Maintained) -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">

                <div class="relative flex-1">
                    <input v-model="search" type="text"
                        placeholder="Search by name, origin, or registration ID..."
                        :class="[
                            'w-full p-2 pl-8 rounded-lg border text-sm transition-colors duration-200',
                            search
                                ? 'border-gray-800 bg-white ring-1 ring-gray-800'
                                : 'border-gray-300 bg-white focus:border-gray-400'
                        ]" />
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4"
                        :class="search ? 'text-gray-800' : 'text-gray-400'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span v-if="search"
                        class="absolute right-2.5 top-2 text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                        searching...
                    </span>
                </div>

                <!-- Bell -->
                <div class="relative" ref="bellRef">
                    <button @click="toggleNotifications" class="relative focus:outline-none">
                        <FontAwesomeIcon icon="bell" class="text-gray-700 text-lg" />
                        <span v-if="pendingFees > 0"
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center">
                            {{ pendingFees > 9 ? '9+' : pendingFees }}
                        </span>
                    </button>

                    <div v-if="showNotifications"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800 text-sm">Notifications</h3>
                            <span v-if="pendingFees > 0"
                                class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ pendingFees }} new
                            </span>
                        </div>
                        <div class="max-h-72 overflow-y-auto">
                            <div v-if="pendingFees > 0"
                                class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-50">
                                <div class="mt-0.5 flex-shrink-0">
                                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ pendingFees }} unpaid environmental fee(s)
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        These registrations are incomplete. Please collect payment.
                                    </p>
                                    <button
                                        @click="feeStatus = 'Pending'; showNotifications = false; applyFilters()"
                                        class="text-xs text-yellow-600 font-semibold mt-1 inline-block hover:underline">
                                        Show Pending Records →
                                    </button>
                                </div>
                            </div>
                            <div v-if="pendingFees === 0"
                                class="px-4 py-8 text-center text-gray-400 text-sm">
                                <FontAwesomeIcon icon="bell" class="text-gray-300 text-2xl mb-2 block mx-auto" />
                                <p>No new notifications</p>
                            </div>
                        </div>
                    </div>
                </div>

                <FontAwesomeIcon icon="user" class="text-gray-700" />
            </div>
        </div>

        <div class="p-4 mt-4 rounded-lg flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h1 class="text-lg font-semibold text-gray-800">System Setting</h1>
            </div>
        </div>

        <!-- NAVIGATION TABS -->
        <div
            class="border-b border-gray-300 flex flex-wrap justify-start sm:justify-center gap-3 sm:gap-6 px-3 sm:px-0 overflow-x-auto whitespace-nowrap">

            <Link v-if="can('view_system_settings')" :href="route('settings')" :class="navClass('settings')"
                class="text-sm sm:text-base">
                General Settings
            </Link>

            <Link v-if="can('view_user_management')" :href="route('usermanagement')" :class="navClass('usermanagement')"
                class="text-sm sm:text-base">
                User Management
            </Link>

            <Link v-if="can('view_audit_logs')" :href="route('auditlogs')" :class="navClass('auditlogs')"
                class="text-sm sm:text-base">
                Audit Logs
            </Link>

            <Link v-if="can('view_website_content')" :href="route('websitecontent')" :class="navClass('websitecontent')"
                class="text-sm sm:text-base">
                Website Content
            </Link>

            <Link v-if="can('view_security')" :href="route('securitysettings')" :class="navClass('securitysettings')"
                class="text-sm sm:text-base">
                Security
            </Link>

        </div>

        <div class="mt-5 p-4 sm:p-6">

            <!-- HEADER -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">

                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Audit Logs</h1>
                    <p class="text-xs text-gray-400">
                        Viewing real-time administrative logs from the database.
                    </p>
                </div>

                <!-- FILTERS -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">

                    <select v-model="filterForm.user_id"
                        class="border border-gray-300 p-2 rounded-lg text-sm w-full sm:w-auto">
                        <option value="">All Users</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                        </option>
                    </select>

                    <select v-model="filterForm.module"
                        class="border border-gray-300 p-2 rounded-lg text-sm w-full sm:w-auto">
                        <option value="">All Modules</option>
                        <option v-for="mod in modules" :key="mod" :value="mod">
                            {{ mod }}
                        </option>
                    </select>

                    <button @click="resetFilters" class="text-xs text-blue-500 hover:underline w-fit">
                        Reset
                    </button>

                </div>

            </div>

            <!-- TABLE -->
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">

                <table class="min-w-[700px] w-full text-left border-collapse">

                    <thead>
                        <tr class="bg-gray-50 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                            <th class="px-4 py-3">User & Role</th>
                            <th class="px-4 py-3">Module</th>
                            <th class="px-4 py-3">Date & Time</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">IP Address</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 text-sm">

                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">
                                        {{ log.user }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 uppercase font-semibold">
                                        {{ log.role }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-gray-600 font-medium">
                                {{ log.module }}
                            </td>

                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ log.created_at }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span :class="getActionColor(log.action)" class="text-xs font-bold uppercase">
                                        {{ log.action }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 italic">
                                        {{ log.description }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">
                                {{ log.ip_address }}
                            </td>

                        </tr>

                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">
                                No logs found in database.
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            <!-- PAGINATION -->
            <div v-if="logs.links.length > 3" class="mt-6 flex flex-wrap justify-center gap-1">

                <Link v-for="link in logs.links" :key="link.label" :href="link.url || '#'" v-html="link.label" :class="[
                    'px-3 py-1 text-xs border rounded-md transition-all',
                    link.active
                        ? 'bg-gray-900 text-white border-gray-900 font-bold'
                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50',
                    !link.url ? 'opacity-30 pointer-events-none' : ''
                ]" />

            </div>

        </div>
    </LandingLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({
    logs: Object,
    filters: Object,
    users: Array,
    modules: Array
});

// ── FILTER LOGIC ──────────────────────────────────────────────────────────────
const filterForm = ref({
    user_id: props.filters?.user_id || '',
    module: props.filters?.module || '',
    search: props.filters?.search || '',
});

// Manual Debounce for typing
let timeout = null;
watch(filterForm, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('auditlogs'), filterForm.value, { preserveState: true, replace: true });
    }, 400);
}, { deep: true });

const resetFilters = () => {
    filterForm.value = { user_id: '', module: '', search: '' };
};

// ── UI HELPERS ───────────────────────────────────────────────────────────────
const getActionColor = (action) => {
    const act = action.toLowerCase();
    if (act.includes('created') || act.includes('collected')) return 'text-emerald-600';
    if (act.includes('deleted')) return 'text-red-600';
    if (act.includes('updated')) return 'text-blue-600';
    if (act.includes('login')) return 'text-amber-600';
    return 'text-gray-600';
};

const navClass = (routeName) => [
    'pb-2 text-sm font-semibold transition border-b-2',
    route().current(routeName) ? 'text-gray-900 border-gray-900' : 'text-gray-400 border-transparent hover:text-gray-600'
];

// ── PERMISSION CHECK ──────────────────────────────────────────────────────────
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const userRole = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase());

const can = (permission) => {
    if (userRole.value === 'admin') return true;
    return permissions.value.includes(permission);
};
</script>