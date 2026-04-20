<template>
    <LandingLayout>
        <!-- Header Section (UI Maintained) -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
                <div class="relative flex-1">
                    <input type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
                </div> 
                <span class="cursor-pointer">🔔</span>
                <span class="cursor-pointer">👤</span>
            </div>
        </div>

        <div class="p-4 mt-4 rounded-lg flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">System Setting</h1>
                    <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
                </div>
                <input type="text" placeholder="Search settings..." class="w-25 p-2 rounded-lg border-2 border-gray-200 focus:border-gray-300 focus:ring-0" />
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-300 flex justify-center gap-6">
            <Link v-if="can('view_system_settings')" :href="route('settings')" :class="navClass('settings')">General Settings</Link>
            <Link v-if="can('view_user_management')" :href="route('usermanagement')" :class="navClass('usermanagement')">User Management</Link>
            <Link v-if="can('view_audit_logs')" :href="route('auditlogs')" :class="navClass('auditlogs')">Audit Logs</Link>
            <Link v-if="can('view_website_content')" :href="route('websitecontent')" :class="navClass('websitecontent')">Website Content</Link>
            <Link v-if="can('view_virtual_tour')" :href="route('virtualtour')" :class="navClass('virtualtour')">Virtual Tour</Link>
            <Link v-if="can('view_security')" :href="route('securitysettings')" :class="navClass('securitysettings')">Security</Link>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">
            <!-- COLUMN 1: PASSWORD UPDATE (Enabled for every user to change their own) -->
            <div class="mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 h-full">
                    <form @submit.prevent="updatePassword" class="grid grid-cols-1 gap-4 mt-2 p-4">
                        <div>
                            <h2 class="block text-black text-lg font-bold">Account Password</h2>
                            <p class="text-sm text-gray-500 mb-2">Change your password to protect your account.</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold uppercase">Current Password</label>
                            <input v-model="passwordForm.current_password" class="border border-gray-400 rounded w-full py-2 px-3 focus:outline-none focus:border-black" type="password">
                            <p v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.current_password }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold uppercase">New Password</label>
                            <input v-model="passwordForm.password" class="border border-gray-400 rounded w-full py-2 px-3 focus:outline-none focus:border-black" type="password">
                            <p v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password }}</p>
                        </div>
                        <button type="submit" :disabled="passwordForm.processing" class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition">
                            {{ passwordForm.processing ? 'Saving...' : 'Update Password' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- COLUMN 2: SESSION MANAGEMENT (Now Protected by edit_security) -->
            <div class="mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-9 h-full">
                    <header class="mb-5">
                        <h2 class="text-lg font-bold">Session Management</h2>
                        <p class="text-gray-400 font-normal">Manage active sessions and secure your account</p>
                    </header>
                    <section class="mb-10">
                        <h3 class="text-md font-bold mb-3">Current Device</h3>
                        <div class="border-t border-gray-100 pt-6 flex items-start gap-4">
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">💻</div>
                            <div class="flex-1">
                                <h4 class="font-bold text-base leading-tight">Windows — Chrome</h4>
                                <div class="text-sm text-gray-400 mt-1">Localhost <span class="text-green-500 font-medium ml-1">[Active Now]</span></div>
                            </div>
                        </div>
                    </section>
                    <div class="flex justify-end gap-4 mt-auto">
                        <button 
                            @click="logoutOtherSessions" 
                            :disabled="!can('edit_security')"
                            :class="[
                                'text-sm font-bold px-5 py-2 rounded-xl transition disabled:opacity-50',
                                !can('edit_security') 
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                                    : 'bg-gray-900 text-white hover:bg-red-600'
                            ]"
                            :title="!can('edit_security') ? 'Access Denied: Cannot manage sessions' : ''"
                        >
                            Logout Other Sessions
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROLE-BASED ACCESS CONTROL (Protected) -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-9 mx-4">
            <header class="mb-6">
                <h2 class="text-md font-bold">Role-Based Access Control</h2>
                <p class="text-gray-400 text-sm">Manage permissions for each module and user role</p>
            </header>

            <div class="flex gap-3 mb-8 overflow-x-auto pb-2">
                <button v-for="tab in modules" :key="tab" @click="activeTab = tab"
                    :class="['px-5 py-2 rounded-lg border text-sm font-medium transition-colors whitespace-nowrap',
                        activeTab === tab ? 'bg-indigo-900 border-indigo-900 text-white' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50']">
                    {{ tab }}
                </button>
            </div>

            <div class="overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="py-4 px-4 font-bold text-gray-700 rounded-l-xl">Role</th>
                            <th class="py-4 px-4 font-bold text-center text-gray-700">View</th>
                            <th class="py-4 px-4 font-bold text-center text-gray-700">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="role in rbacRoles" :key="role.id" class="border-b border-gray-100 last:border-0">
                            <td class="py-5 px-4 flex items-center gap-4">
                                <div :class="['w-10 h-10 rounded-full', role.colorClass]"></div>
                                <span class="font-semibold text-gray-800 uppercase text-xs">{{ role.name }}</span>
                                <span v-if="role.name.toLowerCase() === 'admin'" class="text-[10px] text-gray-400 italic">(Locked)</span>
                            </td>
                            
                            <!-- VIEW TOGGLE -->
                            <td class="py-5 px-4 text-center">
                                <label :class="['relative inline-flex items-center', (role.name.toLowerCase() === 'admin' || !can('edit_security')) ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer']">
                                    <input type="checkbox" v-model="role.modulePermissions[activeTab].view" @change="handleViewChange(role)" :disabled="role.name.toLowerCase() === 'admin' || !can('edit_security')" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                                </label>
                            </td>

                            <!-- EDIT TOGGLE -->
                            <td class="py-5 px-4 text-center">
                                <label :class="['relative inline-flex items-center', (role.name.toLowerCase() === 'admin' || !can('edit_security')) ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer']">
                                    <input type="checkbox" v-model="role.modulePermissions[activeTab].edit" @change="handleEditChange(role)" :disabled="role.name.toLowerCase() === 'admin' || !can('edit_security')" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-4 mt-10">
                <button @click="savePermissions" :disabled="saving || !can('edit_security')"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50">
                    {{ saving ? 'Saving...' : 'Save Permissions' }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">
            <!-- COLUMN 1: LOGIN SECURITY (Functional Toggles) -->
            <div class="mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 h-full">
                    <div class="grid grid-cols-1 gap-6 p-4">
                        <header>
                            <h2 class="block text-black text-lg font-bold">Login Security</h2>
                            <p class="text-sm text-gray-500">Protect accounts from unauthorized access.</p>
                        </header>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-black">Two-Factor Authentication</p>
                                <p class="text-xs text-gray-500">Add an extra layer of security.</p>
                            </div>
                            <label :class="['relative inline-flex items-center', !can('edit_security') ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer']">
                                <input type="checkbox" v-model="securityForm.two_factor" :disabled="!can('edit_security')" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-black">Require Strong Passwords</p>
                                <p class="text-xs text-gray-500">Uppercase, numbers, symbols.</p>
                            </div>
                            <label :class="['relative inline-flex items-center', !can('edit_security') ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer']">
                                <input type="checkbox" v-model="securityForm.strong_password" :disabled="!can('edit_security')" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>

                        <button @click="saveSecuritySettings" :disabled="securityForm.processing || !can('edit_security')" 
                            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition mt-4 disabled:opacity-50">
                            Save Security Policies
                        </button>
                    </div>
                </div>
            </div>

            <!-- COLUMN 2: ACTIVITY MONITORING -->
            <div class="mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-9 h-full">
                    <header>
                        <h2 class="block text-black text-lg font-bold">Activity Monitoring</h2>
                        <p class="text-sm text-gray-500 mb-2">Track system activity and user actions.</p>
                    </header>

                    <section class="mt-6">
                        <p class="text-md font-bold mb-3 border-b border-gray-100 pb-2">Recent Security Events</p>
                        <div v-if="!recentActivities.length" class="text-xs text-gray-400 italic py-4">No recent activity.</div>
                        <div v-for="activity in recentActivities" :key="activity.id" class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-black">{{ activity.action }}</p>
                                <p class="text-[10px] text-gray-400">By: {{ activity.user }}</p>
                            </div>
                            <p class="text-xs text-gray-400 font-semibold">{{ activity.time_ago }}</p>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, useForm, router, usePage } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({
    roles: Array,
    modules: Array,
    recentActivities: Array,
    securitySettings: Object
});

// ── STATE & RBAC INITIALIZATION ──────────────────────────────────────────────
const activeTab = ref(props.modules?.[0] || 'Dashboard'); 
const saving = ref(false);
const rbacRoles = ref(JSON.parse(JSON.stringify(props.roles || [])));

watch(() => props.roles, (newRoles) => {
    rbacRoles.value = JSON.parse(JSON.stringify(newRoles));
}, { deep: true });

// ── PERMISSION HELPER ────────────────────────────────────────────────────────
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const userRole = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase());

const can = (permission) => {
    if (userRole.value === 'admin') return true;
    return permissions.value.includes(permission);
};

// ── TOGGLE LOGIC ─────────────────────────────────────────────────────────────
const handleEditChange = (role) => {
    if (role.modulePermissions[activeTab.value].edit) {
        role.modulePermissions[activeTab.value].view = true;
    }
};

const handleViewChange = (role) => {
    if (!role.modulePermissions[activeTab.value].view) {
        role.modulePermissions[activeTab.value].edit = false;
    }
};

// ── ACTIONS ──────────────────────────────────────────────────────────────────
const savePermissions = () => {
    saving.value = true;
    router.post(route('security.rbac.update'), { roles: rbacRoles.value }, {
        preserveScroll: true,
        preserveState: false,
        onFinish: () => { saving.value = false; }
    });
};

const securityForm = useForm({
    two_factor: props.securitySettings?.two_factor_enabled ?? false,
    strong_password: props.securitySettings?.require_strong_password ?? true,
    login_attempts: 3,
    lockout_duration: 15,
});

const saveSecuritySettings = () => {
    securityForm.post(route('security.settings.update'), { preserveScroll: true });
};

const passwordForm = useForm({ current_password: '', password: '' });
const updatePassword = () => {
    passwordForm.post(route('security.password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const logoutOtherSessions = () => {
    if (confirm('Are you sure you want to log out from all other devices?')) {
        router.post(route('security.sessions.logout_others'));
    }
};

const navClass = (routeName) => [
  'pb-2 text-sm font-semibold transition border-b-2',
  route().current(routeName) ? 'text-gray-900 border-gray-900' : 'text-gray-400 border-transparent hover:text-gray-600'
]
</script>