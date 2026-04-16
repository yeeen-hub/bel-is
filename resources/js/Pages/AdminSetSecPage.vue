<template>
    <LandingLayout>
        <!-- Top Bar -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
                <div class="relative flex-1">
                    <input type="text" placeholder="Search..."
                        class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
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
            </div>
        </div>

        <!-- Navigation Tabs — use underscore permission names -->
        <div class="border-b border-gray-300 flex justify-center gap-6">
            <Link v-if="can('view_system_settings')" :href="route('settings')"         :class="navClass('settings')">General Settings</Link>
            <Link v-if="can('view_user_management')" :href="route('usermanagement')"   :class="navClass('usermanagement')">User Management</Link>
            <Link v-if="can('view_audit_logs')"      :href="route('auditlogs')"         :class="navClass('auditlogs')">Audit Logs</Link>
            <Link v-if="can('view_website_content')" :href="route('websitecontent')"   :class="navClass('websitecontent')">Website Content</Link>
            <Link v-if="can('view_virtual_tour')"    :href="route('virtualtour')"      :class="navClass('virtualtour')">Virtual Tour</Link>
            <Link v-if="can('view_security')"        :href="route('securitysettings')" :class="navClass('securitysettings')">Security</Link>
        </div>

        <!-- Flash messages -->
        <div v-if="$page.props.flash?.success"
            class="mt-4 mx-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error"
            class="mt-4 mx-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
            {{ $page.props.flash.error }}
        </div>

        <!-- RBAC Card -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-9 mx-4 mt-10">
            <header class="mb-6">
                <h2 class="text-md font-bold">Role-Based Access Control</h2>
                <p class="text-gray-400 text-sm">Manage permissions for each module and user role.</p>
            </header>

            <!-- Module tabs -->
            <div class="flex gap-3 mb-8 overflow-x-auto pb-2">
                <button v-for="tab in modules" :key="tab" @click="activeTab = tab"
                    :class="['px-5 py-2 rounded-lg border text-sm font-medium transition-colors whitespace-nowrap',
                        activeTab === tab
                            ? 'bg-gray-900 border-gray-900 text-white'
                            : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50']">
                    {{ tab }}
                </button>
            </div>

            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="py-4 px-4 font-bold text-gray-700">Role</th>
                        <th class="py-4 px-4 font-bold text-gray-700 text-center">View</th>
                        <th class="py-4 px-4 font-bold text-gray-700 text-center">Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="role in rbacRoles" :key="role.id" class="border-b border-gray-100 last:border-0">
                        <td class="py-5 px-4 flex items-center gap-4">
                            <div :class="['w-10 h-10 rounded-full', role.colorClass]"></div>
                            <span class="font-semibold text-gray-800 uppercase text-xs">{{ role.name }}</span>
                        </td>
                        
                        <!-- View Toggle -->
                        <td class="py-5 px-4 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                    v-model="role.modulePermissions[activeTab].view" 
                                    :disabled="role.name.toLowerCase() === 'admin'"
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-disabled:opacity-50"></div>
                            </label>
                        </td>

                        <!-- Edit Toggle -->
                        <td class="py-5 px-4 text-center">
                            <label :class="['relative inline-flex items-center', role.name.toLowerCase() === 'admin' ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer']">
                                <input type="checkbox"
                                    v-model="role.modulePermissions[activeTab].edit"
                                    @change="handleEditChange(role)" 
                                    :disabled="role.name.toLowerCase() === 'admin'"
                                    class="sr-only peer" />
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-end mt-10">
                <button @click="savePermissions" :disabled="saving"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50">
                    {{ saving ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>
        </div>

        <!-- Password + Activity -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">
            <div class="mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 h-full">
                    <form @submit.prevent="updatePassword" class="grid grid-cols-1 gap-4 mt-2 p-4">
                        <h2 class="text-black text-lg font-bold">Password</h2>
                        <div>
                            <input v-model="passwordForm.current_password"
                                class="border border-gray-400 rounded w-full py-2 px-3 focus:outline-none"
                                type="password" placeholder="Current Password" />
                            <p v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">
                                {{ passwordForm.errors.current_password }}
                            </p>
                        </div>
                        <div>
                            <input v-model="passwordForm.password"
                                class="border border-gray-400 rounded w-full py-2 px-3 focus:outline-none"
                                type="password" placeholder="New Password (min. 8)" />
                            <p v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">
                                {{ passwordForm.errors.password }}
                            </p>
                        </div>
                        <button type="submit" :disabled="passwordForm.processing"
                            class="bg-gray-900 text-white font-bold px-5 py-2 rounded-xl disabled:opacity-50">
                            {{ passwordForm.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-10">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-9 h-full">
                    <h2 class="text-black text-lg font-bold mb-3">Activity Monitoring</h2>
                    <div v-if="!recentActivities?.length" class="text-gray-400 text-sm">No recent activity.</div>
                    <div v-for="activity in recentActivities" :key="activity.id"
                        class="flex justify-between py-3 border-b border-gray-50 last:border-0">
                        <div>
                            <span class="text-sm font-medium">{{ activity.action }}</span>
                            <p class="text-xs text-gray-400">{{ activity.user }}</p>
                        </div>
                        <p class="text-xs text-gray-400 ml-4 shrink-0">{{ activity.time_ago }}</p>
                    </div>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, useForm, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    roles:            { type: Array,  default: () => [] },
    modules:          { type: Array,  default: () => [] },
    recentActivities: { type: Array,  default: () => [] },
})

const activeTab = ref(props.modules?.[0] ?? 'Dashboard')

// ── Deep-clone so Vue can track toggle changes independently ──────────────────
const rbacRoles = ref(JSON.parse(JSON.stringify(props.roles)))

// ── Sync local state when server sends confirmed data back after save ──────────
// This is what prevents revert: we update from the server's confirmed state.
watch(() => props.roles, (newRoles) => {
    rbacRoles.value = JSON.parse(JSON.stringify(newRoles))
}, { deep: true })

// ── Permission check — underscore format matching DB ──────────────────────────
const page        = usePage()
const permissions = () => page.props.auth?.permissions ?? []
const userRole    = () => (page.props.auth?.user?.role ?? '').toLowerCase()

const can = (permission) => {
    if (userRole() === 'admin') return true
    return permissions().includes(permission)
}

// ── Save RBAC ─────────────────────────────────────────────────────────────────
const saving = ref(false)

const savePermissions = () => {
    saving.value = true
    router.post(route('security.rbac.update'), { 
        roles: rbacRoles.value 
    }, {
        preserveScroll: true,
        preserveState: false, // Forces Vue to refresh data from server
        onFinish: () => { 
            saving.value = false 
            // Optional: check if props.roles updated
        },
    })
}

// ── Password ──────────────────────────────────────────────────────────────────
const passwordForm = useForm({ current_password: '', password: '' })

const updatePassword = () => {
    passwordForm.post(route('security.password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}

// ── Nav tab helper ────────────────────────────────────────────────────────────
const navClass = (routeName) => [
    'pb-2 text-sm font-semibold transition border-b-2',
    route().current(routeName)
        ? 'text-gray-900 border-gray-900'
        : 'text-gray-400 border-transparent hover:text-gray-600',
]

// Function to ensure "View" is checked if "Edit" is enabled
const handleEditChange = (role) => {
    if (role.modulePermissions[activeTab.value].edit) {
        role.modulePermissions[activeTab.value].view = true;
    }
};
</script>