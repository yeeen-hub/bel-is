<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({ sitios: { type: Array, default: () => [] } })

const page        = usePage()
const permissions = computed(() => page.props.auth?.permissions ?? [])
const userRole    = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase())
const can         = (p) => userRole.value === 'admin' || permissions.value.includes(p)

// ── Add form ──────────────────────────────────────────────────────────────────
const showAdd = ref(false)
const addForm = useForm({ name: '', description: '' })
const submitAdd = () => {
    addForm.post(route('sitios.store'), {
        preserveScroll: true,
        onSuccess: () => { addForm.reset(); showAdd.value = false }
    })
}

// ── Edit form ─────────────────────────────────────────────────────────────────
const editId   = ref(null)
const editForm = useForm({ name: '', description: '', is_active: true })

const startEdit = (s) => {
    editId.value         = s.id
    editForm.name        = s.name
    editForm.description = s.description ?? ''
    editForm.is_active   = s.is_active
}
const cancelEdit = () => { editId.value = null; editForm.reset() }

const submitEdit = (id) => {
    editForm.patch(route('sitios.update', id), {
        preserveScroll: true,
        onSuccess: () => { editId.value = null }
    })
}

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteForm = useForm({})
const deleteSitio = (id) => {
    if (!confirm('Delete this sitio? Attractions linked to it will have their location unset.')) return
    deleteForm.delete(route('sitios.destroy', id), { preserveScroll: true })
}

// ── Nav helper ────────────────────────────────────────────────────────────────
const navClass = (routeName) => [
    'pb-2 text-sm font-semibold transition border-b-2',
    route().current(routeName)
        ? 'text-gray-900 border-gray-900'
        : 'text-gray-400 border-transparent hover:text-gray-600',
]
</script>

<template>
    <LandingLayout>
        <!-- Top Bar -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
                <div class="relative flex-1">
                    <input type="text" placeholder="Search..."
                        class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
                </div>
                <FontAwesomeIcon icon="bell" />
                <FontAwesomeIcon icon="user" />
            </div>
        </div>

        <div class="p-4 mt-4 rounded-lg flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Sitio Management</h1>
                    <p class="text-sm text-gray-500">Manage the list of sitios in Barangay Bel-is.</p>
                </div>
            </div>
        </div>

        <!-- Nav Tabs (same pattern as settings pages) -->
        <div class="border-b border-gray-300 flex justify-center gap-6">
            <Link :href="route('sitios')"               :class="navClass('sitios')">Sitio List</Link>
            <Link :href="route('barangay-attractions')"  :class="navClass('barangay-attractions')">Attraction Management</Link>
        </div>

        <div class="max-w-3xl mx-auto mt-8 space-y-6">

            <!-- Add new sitio -->
            <div v-if="can('edit_system_settings')" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-semibold text-gray-800">Add New Sitio</p>
                    <button v-if="!showAdd" @click="showAdd = true"
                        type="button"
                        class="border border-blue-500 text-blue-500 text-sm font-semibold px-4 py-1.5 rounded-xl hover:bg-blue-500 hover:text-white transition">
                        + Add
                    </button>
                    <button v-else @click="showAdd = false"
                        type="button"
                        class="border border-gray-400 text-gray-500 text-sm font-semibold px-4 py-1.5 rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>
                </div>

                <form v-if="showAdd" @submit.prevent="submitAdd" class="space-y-3">
                    <div>
                        <label class="block text-gray-700 text-xs font-semibold mb-1">Sitio Name *</label>
                        <input v-model="addForm.name" type="text" placeholder="e.g. Sitio Camia"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" />
                        <p v-if="addForm.errors.name" class="text-red-500 text-xs mt-1">{{ addForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-semibold mb-1">Description (optional)</label>
                        <textarea v-model="addForm.description" rows="2"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                            placeholder="Short description..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="addForm.processing"
                            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50">
                            {{ addForm.processing ? 'Saving...' : 'Save Sitio' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sitio list table -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-800">All Sitios
                        <span class="ml-2 text-xs text-gray-400 font-normal">({{ sitios.length }})</span>
                    </p>
                </div>

                <div v-if="sitios.length === 0" class="px-6 py-10 text-center text-gray-400 text-sm">
                    No sitios added yet.
                </div>

                <table v-else class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-3 font-semibold text-gray-700">Sitio Name</th>
                            <th class="p-3 font-semibold text-gray-700">Description</th>
                            <th class="p-3 font-semibold text-gray-700">Status</th>
                            <th v-if="can('edit_system_settings')" class="p-3 font-semibold text-gray-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in sitios" :key="s.id" class="border-b border-gray-100 last:border-0 hover:bg-gray-50">

                            <!-- View mode -->
                            <template v-if="editId !== s.id">
                                <td class="p-3 font-medium text-gray-800">{{ s.name }}</td>
                                <td class="p-3 text-gray-500">{{ s.description || '—' }}</td>
                                <td class="p-3">
                                    <span :class="s.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-500'"
                                        class="text-xs font-semibold px-2 py-0.5 rounded-full">
                                        {{ s.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td v-if="can('edit_system_settings')" class="p-3 text-right space-x-2">
                                    <button @click="startEdit(s)"
                                        class="text-xs font-semibold text-blue-600 hover:underline">Edit</button>
                                    <button @click="deleteSitio(s.id)"
                                        class="text-xs font-semibold text-red-500 hover:underline">Delete</button>
                                </td>
                            </template>

                            <!-- Edit mode (inline) -->
                            <template v-else>
                                <td class="p-2">
                                    <input v-model="editForm.name" type="text"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none" />
                                    <p v-if="editForm.errors.name" class="text-red-500 text-xs mt-0.5">{{ editForm.errors.name }}</p>
                                </td>
                                <td class="p-2">
                                    <input v-model="editForm.description" type="text"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none" placeholder="Description" />
                                </td>
                                <td class="p-2">
                                    <select v-model="editForm.is_active"
                                        class="border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none">
                                        <option :value="true">Active</option>
                                        <option :value="false">Inactive</option>
                                    </select>
                                </td>
                                <td class="p-2 text-right space-x-2">
                                    <button @click="submitEdit(s.id)" :disabled="editForm.processing"
                                        class="text-xs font-bold text-green-600 hover:underline disabled:opacity-50">Save</button>
                                    <button @click="cancelEdit"
                                        class="text-xs font-bold text-gray-400 hover:underline">Cancel</button>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </LandingLayout>
</template>