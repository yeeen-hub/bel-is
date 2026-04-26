<template>
    <LandingLayout>
        <!-- Top Navigation / Search -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">

                <div class="relative flex-1">
                    <input v-model="search" type="text" placeholder="Search by name, origin, or registration ID..."
                        :class="[
                            'w-full p-2 pl-8 rounded-lg border text-sm transition-colors duration-200',
                            search
                                ? 'border-gray-800 bg-white ring-1 ring-gray-800'
                                : 'border-gray-300 bg-white focus:border-gray-400'
                        ]" />
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4" :class="search ? 'text-gray-800' : 'text-gray-400'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
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
                                    <!-- FIX 7: applyFilters removed — just close notification and link to records -->
                                    <Link :href="route('visitor-records')"
                                        @click="showNotifications = false"
                                        class="text-xs text-yellow-600 font-semibold mt-1 inline-block hover:underline">
                                        Show Pending Records →
                                    </Link>
                                </div>
                            </div>
                            <div v-if="pendingFees === 0" class="px-4 py-8 text-center text-gray-400 text-sm">
                                <FontAwesomeIcon icon="bell" class="text-gray-300 text-2xl mb-2 block mx-auto" />
                                <p>No new notifications</p>
                            </div>
                        </div>
                    </div>
                </div>

                <FontAwesomeIcon icon="user" class="text-gray-700" />
            </div>
        </div>

        <!-- Title Section -->
        <div class="p-4 mt-4 rounded-lg flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">System Setting</h1>
                    <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-300 flex flex-wrap justify-start sm:justify-center gap-3 sm:gap-6 px-3 sm:px-0 overflow-x-auto whitespace-nowrap">
            <Link v-if="can('view_system_settings')" :href="route('settings')" :class="navClass('settings')" class="text-sm sm:text-base">
                General Settings
            </Link>
            <Link v-if="can('view_user_management')" :href="route('usermanagement')" :class="navClass('usermanagement')" class="text-sm sm:text-base">
                User Management
            </Link>
            <Link v-if="can('view_audit_logs')" :href="route('auditlogs')" :class="navClass('auditlogs')" class="text-sm sm:text-base">
                Audit Logs
            </Link>
            <Link v-if="can('view_website_content')" :href="route('websitecontent')" :class="navClass('websitecontent')" class="text-sm sm:text-base">
                Website Content
            </Link>
            <Link v-if="can('view_security')" :href="route('securitysettings')" :class="navClass('securitysettings')" class="text-sm sm:text-base">
                Security
            </Link>
        </div>

        <!-- Bulk Action Row -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full mt-5 px-4 gap-3">

            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                <h1 class="text-lg font-semibold text-gray-800">All Users</h1>

                <transition name="fade">
                    <button v-if="selectedUsers.length > 0" @click="openDeleteModal" type="button"
                        class="flex items-center justify-center gap-2 border border-red-400 text-red-500 text-xs font-bold px-4 py-2 rounded-lg hover:bg-red-500 hover:text-white transition-all shadow-sm w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                        Delete Selected ({{ selectedUsers.length }})
                    </button>
                </transition>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                <input v-model="search" type="text" placeholder="Search..."
                    class="w-full sm:w-48 md:w-56 lg:w-64 p-2 rounded-lg border border-gray-200 focus:border-gray-300 focus:ring-0" />

                <button @click="openAddModal" :class="[
                    'text-sm font-bold px-4 py-2 rounded transition-all w-full sm:w-auto',
                    !can('edit_user_management')
                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed pointer-events-none'
                        : 'h-10 bg-blue-500 text-white hover:bg-blue-600'
                ]" :title="!can('edit_user_management') ? 'Permission Denied: Cannot add users' : ''">
                    Add User
                </button>
            </div>
        </div>

        <!-- User Table -->
        <div class="p-3 sm:p-6 bg-gray-50 min-h-screen">
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="min-w-[900px] w-full text-left border-collapse">

                    <thead>
                        <tr class="bg-gray-50/50 text-xs font-semibold uppercase tracking-wider text-gray-600">
                            <th class="px-4 sm:px-6 py-4 w-10">
                                <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                                    class="rounded border-gray-300" />
                            </th>
                            <th class="px-4 sm:px-6 py-4">Name</th>
                            <th class="px-4 sm:px-6 py-4">E-mail</th>
                            <th class="px-4 sm:px-6 py-4">Contact No.</th>
                            <th class="px-4 sm:px-6 py-4">Role</th>
                            <th class="px-4 sm:px-6 py-4">Status</th>
                            <th class="px-4 sm:px-6 py-4">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in filteredUsers" :key="user.id" :class="[
                            'hover:bg-gray-50 transition-colors',
                            selectedUsers.includes(user.id) ? 'bg-blue-50/40' : ''
                        ]">

                            <td class="px-4 sm:px-6 py-4">
                                <input type="checkbox" v-model="selectedUsers" :value="user.id"
                                    class="rounded border-gray-300 text-blue-600" />
                            </td>

                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <span class="font-medium text-gray-900 text-sm sm:text-base">
                                        {{ user.name }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 sm:px-6 py-4 text-gray-600 text-xs sm:text-sm">{{ user.email }}</td>

                            <td class="px-4 sm:px-6 py-4 text-gray-600 text-xs sm:text-sm">{{ user.contact_no }}</td>

                            <td class="px-4 sm:px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 rounded text-[10px] font-bold uppercase tracking-wider">
                                    {{ user.role }}
                                </span>
                            </td>

                            <td class="px-4 sm:px-6 py-4">
                                <button @click="toggleUserStatus(user)" class="flex items-center gap-2">
                                    <span :class="['h-2 w-2 rounded-full', user.is_active ? 'bg-emerald-500' : 'bg-red-500']"></span>
                                    <span :class="['text-xs sm:text-sm font-medium', user.is_active ? 'text-emerald-600' : 'text-red-600']">
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </button>
                            </td>

                            <!-- FIX 1: Added @click="editUser(user)" to the Edit button -->
                            <td class="px-4 sm:px-6 py-4 text-sm font-semibold">
                                <button
                                    @click="editUser(user)"
                                    :disabled="!can('edit_user_management')"
                                    :class="can('edit_user_management')
                                        ? 'text-blue-500 hover:text-blue-700'
                                        : 'text-gray-300 cursor-not-allowed'"
                                    :title="!can('edit_user_management') ? 'Permission Denied' : 'Edit user'">
                                    Edit
                                </button>
                            </td>

                        </tr>

                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 text-sm">
                                No users found.
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>
        </div>

        <!-- ADD / EDIT USER MODAL -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white p-5 rounded-xl w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">

                <h2 class="text-lg font-bold mb-4">
                    {{ form.id ? 'Edit User' : 'Add New User' }}
                </h2>

                <!-- Global error -->
                <div v-if="form.errors.error" class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm mb-3">
                    {{ form.errors.error }}
                </div>

                <form @submit.prevent="submitForm" class="space-y-3">

                    <div>
                        <input v-model="form.name" type="text" placeholder="Full Name"
                            class="w-full p-2 border rounded" :class="form.errors.name ? 'border-red-400' : ''" />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <input v-model="form.email" type="email" placeholder="Email"
                            class="w-full p-2 border rounded" :class="form.errors.email ? 'border-red-400' : ''" />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <input v-model="form.contact_no" type="text" placeholder="Contact (optional)"
                            class="w-full p-2 border rounded" />
                    </div>

                    <!-- Password — required for new users, optional for edit -->
                    <div>
                        <input v-model="form.password" type="password"
                            :placeholder="form.id ? 'New Password (leave blank to keep current)' : 'Password'"
                            class="w-full p-2 border rounded" :class="form.errors.password ? 'border-red-400' : ''" />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- FIX 3: password_confirmation field added so 'confirmed' rule works -->
                    <div v-if="form.password">
                        <input v-model="form.password_confirmation" type="password"
                            placeholder="Confirm Password"
                            class="w-full p-2 border rounded" :class="form.errors.password_confirmation ? 'border-red-400' : ''" />
                        <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Role</label>
                        <select v-model="form.role" class="w-full p-2 border rounded"
                            :class="form.errors.role ? 'border-red-400' : ''">
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">
                                {{ roleLabel(role.name) }}
                            </option>
                        </select>
                        <p v-if="form.errors.role" class="text-red-500 text-xs mt-1">{{ form.errors.role }}</p>
                        <!-- Show current vs selected for clarity -->
                        <p v-if="form.id && form.role" class="text-xs text-gray-400 mt-1">
                            Selected: <span class="font-semibold text-gray-700">{{ roleLabel(form.role) }}</span>
                        </p>
                    </div>

                    <div>
                        <input v-model="form.admin_password" type="password" placeholder="Your Admin Password (required)"
                            class="w-full p-2 border rounded" :class="form.errors.admin_password ? 'border-red-400' : ''" />
                        <p v-if="form.errors.admin_password" class="text-red-500 text-xs mt-1">{{ form.errors.admin_password }}</p>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-3">
                        <button type="button" @click="closeModal" class="px-4 py-2 text-gray-600 border rounded">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : (form.id ? 'Save Changes' : 'Create User') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60] p-4">
            <div class="bg-white w-full max-w-md rounded-xl shadow-2xl border-t-4 border-red-500 p-5">

                <div class="flex items-center gap-3 text-red-600 mb-4">
                    <h2 class="text-lg sm:text-xl font-bold">Verify Identity</h2>
                </div>

                <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                    You are deleting
                    <span class="font-bold text-gray-900">{{ selectedUsers.length }}</span>
                    user(s). Enter your admin password to confirm.
                </p>

                <form @submit.prevent="confirmDelete" class="space-y-3">
                    <input v-model="deleteForm.admin_password" type="password" placeholder="Your Password"
                        class="w-full p-2 border rounded focus:border-red-400 outline-none" required />

                    <div v-if="deleteForm.errors.admin_password" class="text-red-500 text-xs font-bold">
                        {{ deleteForm.errors.admin_password }}
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-2">
                        <button type="button" @click="showDeleteModal = false"
                            class="w-full sm:w-auto px-4 py-2 text-gray-500 font-medium border rounded">
                            Cancel
                        </button>
                        <button type="submit" :disabled="deleteForm.processing"
                            class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 font-bold disabled:opacity-50">
                            Delete Permanently
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Success Toast -->
        <transition name="fade">
            <div v-if="successMessage"
                class="fixed bottom-6 right-6 z-[100] bg-green-600 text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                </svg>
                {{ successMessage }}
            </div>
        </transition>

    </LandingLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, useForm, router, usePage } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

// ── Auth & permissions ────────────────────────────────────────────────────────
const page        = usePage()
const permissions = computed(() => page.props.auth?.permissions ?? [])
const userRole    = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase())

// FIX 2: can() defined once at top level — not re-defined inside toggleUserStatus
const can = (permission) => {
    if (userRole.value === 'admin') return true
    return permissions.value.includes(permission)
}

// ── Props ─────────────────────────────────────────────────────────────────────
// FIX 4: pendingFees added to defineProps so the bell badge works
const props = defineProps({
    users:       Array,
    roles:       Array,
    pendingFees: { type: Number, default: 0 },
})

// ── State ─────────────────────────────────────────────────────────────────────
const search          = ref('')
const showAddModal    = ref(false)
const showDeleteModal = ref(false)
const selectedUsers   = ref([])

// FIX 5 & 6: showNotifications and bellRef defined at top level
const showNotifications = ref(false)
const bellRef           = ref(null)

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value
}

// ── Click-outside to close bell dropdown ─────────────────────────────────────
const handleClickOutside = (e) => {
    if (bellRef.value && !bellRef.value.contains(e.target)) {
        showNotifications.value = false
    }
}
onMounted(()  => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

// ── Forms ─────────────────────────────────────────────────────────────────────
const form = useForm({
    id:                    null,
    name:                  '',
    email:                 '',
    contact_no:            '',
    password:              '',
    password_confirmation: '',   // FIX 3: needed for 'confirmed' validation rule
    role:                  '',
    admin_password:        '',
})

const deleteForm = useForm({
    ids:            [],
    admin_password: '',
})

// ── Role label helper ─────────────────────────────────────────────────────────
// Converts role slugs to readable labels dynamically — no hardcoded map.
// Works for any role added to the DB in the future.
// e.g. "lgu_official" → "Lgu Official", "barangay_staff" → "Barangay Staff"
const roleLabel = (name) => {
    if (!name) return ''
    return name
        .replace(/_/g, ' ')               // underscores → spaces
        .replace(/-/g, ' ')               // hyphens → spaces
        .replace(/\w/g, c => c.toUpperCase()) // capitalize each word
}

// ── Success toast ──────────────────────────────────────────────────────────────
const successMessage = ref('')
let successTimer = null
const showSuccess = (msg) => {
    successMessage.value = msg
    clearTimeout(successTimer)
    successTimer = setTimeout(() => { successMessage.value = '' }, 3500)
}

// ── Navigation tab helper ─────────────────────────────────────────────────────
const navClass = (routeName) => [
    'pb-2 text-sm font-semibold transition border-b-2',
    route().current(routeName)
        ? 'text-gray-900 border-gray-900'
        : 'text-gray-400 border-transparent hover:text-gray-600'
]

// ── Table filter ──────────────────────────────────────────────────────────────
const filteredUsers = computed(() =>
    props.users.filter(user =>
        user.name.toLowerCase().includes(search.value.toLowerCase()) ||
        user.email.toLowerCase().includes(search.value.toLowerCase())
    )
)

// ── Checkbox logic ────────────────────────────────────────────────────────────
const isAllSelected = computed(() =>
    filteredUsers.value.length > 0 &&
    selectedUsers.value.length === filteredUsers.value.length
)

const toggleSelectAll = (e) => {
    selectedUsers.value = e.target.checked
        ? filteredUsers.value.map(u => u.id)
        : []
}

// ── Add user ──────────────────────────────────────────────────────────────────
const openAddModal = () => {
    form.reset()
    form.id = null
    showAddModal.value = true
}

// ── Edit user — FIX 1: this function now wired to the Edit button ─────────────
const editUser = (user) => {
    form.clearErrors()
    form.id                    = user.id
    form.name                  = user.name
    form.email                 = user.email
    form.contact_no            = user.contact_no === 'N/A' ? '' : (user.contact_no ?? '')
    form.role                  = user.role
    form.password              = ''
    form.password_confirmation = ''
    form.admin_password        = ''
    showAddModal.value         = true
}

// ── Submit add/edit ───────────────────────────────────────────────────────────
const submitForm = () => {
    if (form.id) {
        form.patch(route('usermanagement.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
                showSuccess('User updated successfully.')
            },
            onFinish: () => form.reset('admin_password'),
        })
    } else {
        form.post(route('usermanagement.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
                showSuccess('User created successfully.')
            },
            onFinish: () => form.reset('admin_password'),
        })
    }
}

const closeModal = () => {
    showAddModal.value = false
    form.reset()
    form.id = null
}

// ── Bulk delete ───────────────────────────────────────────────────────────────
const openDeleteModal = () => {
    deleteForm.clearErrors()
    deleteForm.admin_password = ''
    deleteForm.ids            = selectedUsers.value
    showDeleteModal.value     = true
}

const confirmDelete = () => {
    deleteForm.post(route('usermanagement.bulk-destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false
            selectedUsers.value   = []
            deleteForm.reset()
        },
    })
}

// ── Toggle active status ──────────────────────────────────────────────────────
// FIX 2: can() no longer re-defined here — uses the top-level definition
const toggleUserStatus = (user) => {
    router.patch(route('usermanagement.toggle', user.id), {}, { preserveScroll: true })
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to     { opacity: 0; }
</style>