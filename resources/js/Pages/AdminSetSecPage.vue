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
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">System Setting</h1>
                    <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
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

            <Link v-if="can('view_virtual_tour')" :href="route('virtualtour')" :class="navClass('virtualtour')"
                class="text-sm sm:text-base">
                Virtual Tour
            </Link>

            <Link v-if="can('view_security')" :href="route('securitysettings')" :class="navClass('securitysettings')"
                class="text-sm sm:text-base">
                Security
            </Link>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 px-4 sm:px-6">

            <!-- COLUMN 1: PASSWORD -->
            <div class="mt-6 md:mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5 h-full">

                    <form @submit.prevent="updatePassword" class="space-y-4">

                        <div>
                            <h2 class="text-black text-base sm:text-lg font-bold">
                                Account Password
                            </h2>
                            <p class="text-sm text-gray-500">
                                Change your password to protect your account.
                            </p>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-xs sm:text-sm font-bold uppercase">
                                Current Password
                            </label>
                            <input v-model="passwordForm.current_password" type="password"
                                class="border border-gray-400 rounded w-full py-2 px-3 text-sm focus:outline-none focus:border-black" />
                            <p v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">
                                {{ passwordForm.errors.current_password }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-xs sm:text-sm font-bold uppercase">
                                New Password
                            </label>
                            <input v-model="passwordForm.password" type="password"
                                class="border border-gray-400 rounded w-full py-2 px-3 text-sm focus:outline-none focus:border-black" />
                            <p v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">
                                {{ passwordForm.errors.password }}
                            </p>
                        </div>

                        <button type="submit" :disabled="passwordForm.processing"
                            class="w-full sm:w-auto bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50">
                            {{ passwordForm.processing ? 'Saving...' : 'Update Password' }}
                        </button>

                    </form>

                </div>
            </div>

            <!-- COLUMN 2: SESSION -->
            <div class="mt-6 md:mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-6 h-full flex flex-col">

                    <header class="mb-5">
                        <h2 class="text-base sm:text-lg font-bold">
                            Session Management
                        </h2>
                        <p class="text-sm text-gray-400">
                            Manage active sessions and secure your account
                        </p>
                    </header>

                    <section class="mb-8 sm:mb-10">
                        <h3 class="text-sm sm:text-md font-bold mb-3">
                            Current Device
                        </h3>

                        <div class="border-t border-gray-100 pt-4 flex items-start gap-3 sm:gap-4">
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-sm">
                                💻
                            </div>

                            <div class="flex-1">
                                <h4 class="font-bold text-sm sm:text-base">
                                    Windows — Chrome
                                </h4>

                                <div class="text-xs sm:text-sm text-gray-400 mt-1">
                                    Localhost
                                    <span class="text-green-500 font-medium ml-1">
                                        [Active Now]
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="mt-auto flex justify-end">
                        <button @click="logoutOtherSessions" :disabled="!can('edit_security')"
                            class="w-full sm:w-auto text-sm font-bold px-5 py-2 rounded-xl transition" :class="can('edit_security')
                                ? 'bg-gray-900 text-white hover:bg-red-600'
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'">
                            Logout Other Sessions
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <!-- ROLE-BASED ACCESS CONTROL (Protected) -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm mt-4 p-4 md:p-9 mx-2 md:mx-2">

            <!-- HEADER -->
            <header class="mb-6">
                <h2 class="text-md font-bold">Role-Based Access Control</h2>
                <p class="text-gray-400 text-sm">Manage permissions for each module and user role</p>
            </header>

            <!-- MODULE TABS -->
            <div class="flex gap-3 mb-6 overflow-x-auto pb-2">
                <button v-for="tab in modules" :key="tab" @click="activeTab = tab"
                    class="px-4 md:px-5 py-2 rounded-lg border text-sm font-medium transition-colors whitespace-nowrap"
                    :class="activeTab === tab
                        ? 'bg-indigo-900 border-indigo-900 text-white'
                        : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50'">
                    {{ tab }}
                </button>
            </div>

            <!-- TABLE WRAPPER (IMPORTANT FOR RESPONSIVENESS) -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left border-collapse">

                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="py-4 px-4 font-bold text-gray-700 rounded-l-xl">Role</th>
                            <th class="py-4 px-4 font-bold text-center text-gray-700">View</th>
                            <th class="py-4 px-4 font-bold text-center text-gray-700">Edit</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="role in rbacRoles" :key="role.id" class="border-b border-gray-100 last:border-0">

                            <!-- ROLE -->
                            <td class="py-4 md:py-5 px-4 flex items-center gap-3 md:gap-4">
                                <div :class="['w-8 h-8 md:w-10 md:h-10 rounded-full', role.colorClass]"></div>
                                <span class="font-semibold text-gray-800 uppercase text-[10px] md:text-xs">
                                    {{ role.name }}
                                </span>
                                <span v-if="role.name.toLowerCase() === 'admin'"
                                    class="text-[10px] text-gray-400 italic hidden sm:inline">
                                    (Locked)
                                </span>
                            </td>

                            <!-- VIEW TOGGLE -->
                            <td class="py-4 md:py-5 px-4 text-center">
                                <label :class="[
                                    'relative inline-flex items-center',
                                    (role.name.toLowerCase() === 'admin' || !can('edit_security'))
                                        ? 'opacity-30 cursor-not-allowed'
                                        : 'cursor-pointer'
                                ]">
                                    <input type="checkbox" v-model="role.modulePermissions[activeTab].view"
                                        @change="handleViewChange(role)"
                                        :disabled="role.name.toLowerCase() === 'admin' || !can('edit_security')"
                                        class="sr-only peer">
                                    <div class="w-10 h-5 md:w-11 md:h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:rounded-full after:h-4 after:w-4 md:after:h-5 md:after:w-5
                                after:transition-all peer-checked:after:translate-x-full">
                                    </div>
                                </label>
                            </td>

                            <!-- EDIT TOGGLE -->
                            <td class="py-4 md:py-5 px-4 text-center">
                                <label :class="[
                                    'relative inline-flex items-center',
                                    (role.name.toLowerCase() === 'admin' || !can('edit_security'))
                                        ? 'opacity-30 cursor-not-allowed'
                                        : 'cursor-pointer'
                                ]">
                                    <input type="checkbox" v-model="role.modulePermissions[activeTab].edit"
                                        @change="handleEditChange(role)"
                                        :disabled="role.name.toLowerCase() === 'admin' || !can('edit_security')"
                                        class="sr-only peer">
                                    <div class="w-10 h-5 md:w-11 md:h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:rounded-full after:h-4 after:w-4 md:after:h-5 md:after:w-5
                                after:transition-all peer-checked:after:translate-x-full">
                                    </div>
                                </label>
                            </td>

                        </tr>
                    </tbody>

                </table>
            </div>

            <!-- SAVE BUTTON -->
            <div class="flex justify-end gap-4 mt-8">
                <button @click="savePermissions" :disabled="saving || !can('edit_security')"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50">
                    {{ saving ? 'Saving...' : 'Save Permissions' }}
                </button>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-2 p-4">

            <!-- COLUMN 1: LOGIN SECURITY -->
            <div class="mt-6 lg:mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 md:p-5 h-full">

                    <div class="grid grid-cols-1 gap-5 md:gap-6 p-2 md:p-4">

                        <!-- HEADER -->
                        <header>
                            <h2 class="text-black text-base md:text-lg font-bold">Login Security</h2>
                            <p class="text-sm text-gray-500">Protect accounts from unauthorized access.</p>
                        </header>

                        <!-- TWO FACTOR -->
                        <div class="flex items-start md:items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-black">Two-Factor Authentication</p>
                                <p class="text-xs text-gray-500">Add an extra layer of security.</p>
                            </div>

                            <label :class="[
                                'relative inline-flex items-center',
                                !can('edit_security') ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer'
                            ]">
                                <input type="checkbox" v-model="securityForm.two_factor"
                                    :disabled="!can('edit_security')" class="sr-only peer">
                                <div class="w-10 h-5 md:w-11 md:h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-500
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:rounded-full after:h-4 after:w-4 md:after:h-5 md:after:w-5
                            after:transition-all peer-checked:after:translate-x-full">
                                </div>
                            </label>
                        </div>

                        <!-- STRONG PASSWORD -->
                        <div class="flex items-start md:items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-black">Require Strong Passwords</p>
                                <p class="text-xs text-gray-500">Uppercase, numbers, symbols.</p>
                            </div>

                            <label :class="[
                                'relative inline-flex items-center',
                                !can('edit_security') ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer'
                            ]">
                                <input type="checkbox" v-model="securityForm.strong_password"
                                    :disabled="!can('edit_security')" class="sr-only peer">
                                <div class="w-10 h-5 md:w-11 md:h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-500
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:rounded-full after:h-4 after:w-4 md:after:h-5 md:after:w-5
                            after:transition-all peer-checked:after:translate-x-full">
                                </div>
                            </label>
                        </div>

                        <!-- BUTTON -->
                        <button @click="saveSecuritySettings"
                            :disabled="securityForm.processing || !can('edit_security')"
                            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition mt-4 disabled:opacity-50 w-full md:w-auto">
                            Save Security Policies
                        </button>

                    </div>
                </div>
            </div>

            <!-- COLUMN 2: ACTIVITY MONITORING -->
            <div class="mt-6 lg:mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 md:p-9 h-full">

                    <header>
                        <h2 class="text-black text-base md:text-lg font-bold">Activity Monitoring</h2>
                        <p class="text-sm text-gray-500 mb-2">Track system activity and user actions.</p>
                    </header>

                    <section class="mt-4 md:mt-6">

                        <p class="text-sm md:text-md font-bold mb-3 border-b border-gray-100 pb-2">
                            Recent Security Events
                        </p>

                        <div v-if="!recentActivities.length" class="text-xs text-gray-400 italic py-4">
                            No recent activity.
                        </div>

                        <div v-for="activity in recentActivities" :key="activity.id"
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 py-3 border-b border-gray-50 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-black">{{ activity.action }}</p>
                                <p class="text-[10px] text-gray-400">By: {{ activity.user }}</p>
                            </div>

                            <p class="text-xs text-gray-400 font-semibold">
                                {{ activity.time_ago }}
                            </p>
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