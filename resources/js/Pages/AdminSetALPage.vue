<template>
    <LandingLayout>
        <!-- Header Section (UI Maintained) -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
                <div class="relative flex-1">
                    <input v-model="filterForm.search" type="text" placeholder="Search by action, target, or IP..." class="w-full p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
                </div> 
                <span class="cursor-pointer">🔔</span>
                <span class="cursor-pointer">👤</span>
            </div>
        </div>

        <div class="p-4 mt-4 rounded-lg flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h1 class="text-lg font-semibold text-gray-800">System Setting</h1>
            </div>
        </div>

        <!-- NAVIGATION TABS -->
        <div class="border-b border-gray-300 flex justify-center gap-6">
            <Link v-if="can('view_system_settings')" :href="route('settings')" :class="navClass('settings')">General Settings</Link>
            <Link v-if="can('view_user_management')" :href="route('usermanagement')" :class="navClass('usermanagement')">User Management</Link>
            <Link v-if="can('view_audit_logs')" :href="route('auditlogs')" :class="navClass('auditlogs')">Audit Logs</Link>
            <Link v-if="can('view_website_content')" :href="route('websitecontent')" :class="navClass('websitecontent')">Website Content</Link>
            <Link v-if="can('view_virtual_tour')" :href="route('virtualtour')" :class="navClass('virtualtour')">Virtual Tour</Link>
            <Link v-if="can('view_security')" :href="route('securitysettings')" :class="navClass('securitysettings')">Security</Link>
        </div>

        <div class="mt-5 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Audit Logs</h1>
                    <p class="text-xs text-gray-400">Viewing real-time administrative logs from the database.</p>
                </div>
                
                <!-- FILTERS -->
                <div class="flex items-center gap-3">
                    <select v-model="filterForm.user_id" class="border border-gray-300 p-1 px-2 rounded-lg text-sm focus:ring-0">
                        <option value="">All Users</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>

                    <select v-model="filterForm.module" class="border border-gray-300 p-1 px-2 rounded-lg text-sm focus:ring-0">
                        <option value="">All Modules</option>
                        <option v-for="mod in modules" :key="mod" :value="mod">{{ mod }}</option>
                    </select>

                    <button @click="resetFilters" class="text-xs text-blue-500 hover:underline">Reset</button>
                </div>
            </div>

            <!-- DATA TABLE -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                            <th class="px-6 py-4">User & Role</th>
                            <th class="px-6 py-4">Module</th>
                            <th class="px-6 py-4">Date & Time</th>
                            <th class="px-6 py-4">Action</th>
                            <th class="px-6 py-4">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 transition-colors text-sm">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ log.user }}</span>
                                    <span class="text-[10px] text-gray-400 uppercase font-semibold">{{ log.role }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-medium">{{ log.module }}</td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ log.created_at }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span :class="getActionColor(log.action)" class="text-xs font-bold uppercase">{{ log.action }}</span>
                                    <span class="text-[10px] text-gray-400 italic">{{ log.description }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-400 font-mono text-xs">{{ log.ip_address }}</td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">No logs found in database.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div v-if="logs.links.length > 3" class="mt-6 flex justify-center gap-1">
                <Link v-for="link in logs.links" :key="link.label" :href="link.url || '#'" 
                    v-html="link.label"
                    :class="['px-3 py-1 text-xs border rounded-md transition-all', 
                        link.active ? 'bg-gray-900 text-white border-gray-900 font-bold' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50',
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