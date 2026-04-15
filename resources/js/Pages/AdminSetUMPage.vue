<template>
    <LandingLayout>
        <!-- Top Navigation / Search -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
                <div class="relative flex-1">
                    <input v-model="search" type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
                </div> 
                <div class="flex gap-4 text-gray-500">
                    <button class="hover:text-gray-800">🔔</button>
                    <button class="hover:text-gray-800">👤</button>
                </div>
            </div>
        </div>

        <!-- Title Section -->
        <div class="p-4 mt-4 rounded-lg flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">System Setting</h1>
                    <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
                </div>
                 <input v-model="search" type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-2 border-gray-200 focus:border-gray-300 focus:ring-0" />
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-300 flex justify-center gap-6">
            <Link :href="route('settings')" :class="navClass('settings')">System Settings</Link>
            <Link :href="route('usermanagement')" :class="navClass('usermanagement')">User Management</Link>
            <Link :href="route('auditlogs')" :class="navClass('auditlogs')">Audit Logs</Link>
            <Link :href="route('websitecontent')" :class="navClass('websitecontent')">Website Content</Link>
            <Link :href="route('virtualtour')" :class="navClass('virtualtour')">Virtual Tour</Link>
            <Link :href="route('securitysettings')" :class="navClass('securitysettings')">Security</Link>
        </div>

        <!-- Bulk Action Row -->
        <div class="flex items-center justify-between w-full mt-5 px-4">
            <div class="flex items-center gap-4">
                <h1 class="text-lg font-semibold text-gray-800">All Users</h1>
                
                <transition name="fade">
                    <button
                        v-if="selectedUsers.length > 0"
                        @click="openDeleteModal"
                        type="button"
                        class="flex items-center gap-2 border border-red-400 text-red-500 text-xs font-bold px-4 py-2 rounded-lg hover:bg-red-500 hover:text-white transition-all shadow-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                        Delete Selected ({{ selectedUsers.length }})
                    </button>
                </transition>
            </div>

            <div class="flex items-center gap-2">
                <input v-model="search" type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
                <button @click="openAddModal" class="h-10 border bg-blue-500 text-white font-bold px-3 text-sm rounded-lg hover:bg-blue-600">
                    Add User
                </button>
            </div>
        </div>

        <!-- User Table -->
        <div class="p-6 bg-gray-50 min-h-screen">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-xs font-semibold uppercase tracking-wider text-gray-600">
                            <th class="px-6 py-4 w-10">
                                <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected" class="rounded border-gray-300" />
                            </th>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">E-mail</th>
                            <th class="px-6 py-4">Contact No.</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in filteredUsers" :key="user.id" 
                            :class="['hover:bg-gray-50 transition-colors', selectedUsers.includes(user.id) ? 'bg-blue-50/40' : '']">
                            <td class="px-6 py-4">
                                <input type="checkbox" v-model="selectedUsers" :value="user.id" class="rounded border-gray-300 text-blue-600" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-sm">{{ user.email }}</td>
                            <td class="px-6 py-4 text-gray-600 text-sm">{{ user.contact_no }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 rounded text-[10px] font-bold uppercase tracking-wider">{{ user.role }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <button @click="toggleUserStatus(user)" class="flex items-center gap-2">
                                    <span :class="['h-2 w-2 rounded-full', user.is_active ? 'bg-emerald-500' : 'bg-red-500']"></span>
                                    <span :class="['text-sm font-medium', user.is_active ? 'text-emerald-600' : 'text-red-600']">
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold">
                                <button @click="editUser(user)" class="text-blue-500 hover:text-blue-700">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ADD/EDIT USER MODAL -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-xl w-96 shadow-xl max-h-[90vh] overflow-y-auto">
                <h2 class="text-xl font-bold mb-4">{{ form.id ? 'Edit User' : 'Add New User' }}</h2>
                <form @submit.prevent="submitForm">
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 uppercase">User Information</label>
                        <input v-model="form.name" type="text" placeholder="Full Name" class="w-full p-2 border rounded" required />
                        <input v-model="form.email" type="email" placeholder="Email Address" class="w-full p-2 border rounded" required />
                        
                        <!-- Contact Number Field -->
                        <input v-model="form.contact_no" type="text" placeholder="Contact Number (e.g., 0912...)" class="w-full p-2 border rounded" />
                        
                        <div class="pt-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase">
                                {{ form.id ? 'Security (Update Only if Needed)' : 'Security' }}
                            </label>
                            <input v-model="form.password" type="password" placeholder="Password" class="w-full p-2 border rounded mt-1" :required="!form.id" />
                            <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" class="w-full p-2 border rounded mt-1" :required="!form.id" />
                        </div>

                        <select v-model="form.role" class="w-full p-2 border rounded" required>
                            <option value="" disabled>Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                        </select>

                        <!-- ADMIN CONFIRMATION FOR CREATE/UPDATE -->
                        <div class="mt-4 pt-4 border-t border-gray-200 bg-blue-50/50 p-3 rounded-lg">
                            <label class="block text-[10px] font-black text-blue-700 uppercase mb-1">Authorize Action</label>
                            <p class="text-[10px] text-blue-600 mb-2 font-medium">Please enter YOUR admin password to proceed.</p>
                            <input v-model="form.admin_password" type="password" placeholder="Your Admin Password" 
                                class="w-full p-2 border-2 border-blue-100 rounded focus:border-blue-500 outline-none bg-white" 
                                required />
                            <div v-if="form.errors.admin_password" class="text-red-500 text-[11px] mt-1 font-bold">{{ form.errors.admin_password }}</div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="closeModal" class="px-4 py-2 text-gray-600">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 shadow-md">
                            {{ form.id ? 'Save Changes' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DELETE CONFIRMATION MODAL -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60]">
            <div class="bg-white p-6 rounded-xl w-96 shadow-2xl border-t-4 border-red-500">
                <div class="flex items-center gap-3 text-red-600 mb-4">
                    <h2 class="text-xl font-bold">Verify Identity</h2>
                </div>
                
                <p class="text-sm text-gray-600 mb-4">
                    You are deleting <span class="font-bold text-gray-900">{{ selectedUsers.length }}</span> user(s). Enter your admin password to confirm.
                </p>

                <form @submit.prevent="confirmDelete">
                    <div class="mb-4">
                        <input 
                            v-model="deleteForm.admin_password" 
                            type="password" 
                            placeholder="Your Password"
                            class="w-full p-2 border-2 border-gray-100 rounded focus:border-red-400 outline-none"
                            required
                        />
                        <div v-if="deleteForm.errors.admin_password" class="text-red-500 text-xs mt-1 font-bold">{{ deleteForm.errors.admin_password }}</div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showDeleteModal = false" class="px-4 py-2 text-gray-500 font-medium">Cancel</button>
                        <button type="submit" :disabled="deleteForm.processing" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 font-bold shadow-lg">
                            Delete Permanently
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LandingLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import LandingLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({
    users: Array,
    roles: Array
});

// State
const search = ref('');
const showAddModal = ref(false);
const showDeleteModal = ref(false);
const selectedUsers = ref([]);

// Main Form object
const form = useForm({
    id: null,
    name: '',
    email: '',
    contact_no: '',
    password: '',
    password_confirmation: '',
    role: '',
    admin_password: '', 
});

// Delete Form object
const deleteForm = useForm({
    ids: [],
    admin_password: '',
});

// Navigation UI Helper
const navClass = (routeName) => [
  'pb-2 text-sm font-semibold transition border-b-2',
  route().current(routeName)
    ? 'text-gray-900 border-gray-900'
    : 'text-gray-400 border-transparent hover:text-gray-600'
];

// Table Filter Logic
const filteredUsers = computed(() => {
    return props.users.filter(user => 
        user.name.toLowerCase().includes(search.value.toLowerCase()) ||
        user.email.toLowerCase().includes(search.value.toLowerCase())
    );
});

// Checkbox Logic
const isAllSelected = computed(() => {
    return filteredUsers.value.length > 0 && selectedUsers.value.length === filteredUsers.value.length;
});

const toggleSelectAll = (e) => {
    selectedUsers.value = e.target.checked ? filteredUsers.value.map(user => user.id) : [];
};

// Add User Logic
const openAddModal = () => {
    form.reset();
    form.id = null;
    showAddModal.value = true;
};

// Edit User Logic
const editUser = (user) => {
    form.clearErrors();
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    // Map contact_no correctly
    form.contact_no = user.contact_no === 'N/A' ? '' : user.contact_no;
    form.role = user.role;
    form.password = '';
    form.password_confirmation = '';
    form.admin_password = '';
    showAddModal.value = true;
};

// Form Submission (Add/Edit)
const submitForm = () => {
    if (form.id) {
        form.patch(route('usermanagement.update', form.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onFinish: () => form.reset('admin_password'),
        });
    } else {
        form.post(route('usermanagement.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onFinish: () => form.reset('admin_password'),
        });
    }
};

const closeModal = () => {
    showAddModal.value = false;
    form.reset();
    form.id = null;
};

// Bulk Delete Logic
const openDeleteModal = () => {
    deleteForm.clearErrors();
    deleteForm.admin_password = '';
    deleteForm.ids = selectedUsers.value;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    deleteForm.post(route('usermanagement.bulk-destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedUsers.value = [];
            deleteForm.reset();
        },
    });
};

const toggleUserStatus = (user) => {
    router.patch(route('usermanagement.toggle', user.id), {}, { preserveScroll: true });
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>