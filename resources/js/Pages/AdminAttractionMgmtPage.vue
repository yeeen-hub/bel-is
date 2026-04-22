<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, Link, router } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    attractions:    { type: Array, default: () => [] },
    sitios:         { type: Array, default: () => [] },
    unreviewed:     { type: Array, default: () => [] },
    unreviewedCount:{ type: Number, default: 0 },
})

const page        = usePage()
const permissions = computed(() => page.props.auth?.permissions ?? [])
const userRole    = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase())
const can         = (p) => userRole.value === 'admin' || permissions.value.includes(p)

const navClass = (routeName) => [
    'pb-2 text-sm font-semibold transition border-b-2',
    route().current(routeName)
        ? 'text-gray-900 border-gray-900'
        : 'text-gray-400 border-transparent hover:text-gray-600',
]

// ── Tab ───────────────────────────────────────────────────────────────────────
const activeTab = ref('list') // 'list' | 'notifications'

// ── Type options ──────────────────────────────────────────────────────────────
const typeOptions = ['Resort', 'Beach', 'Falls', 'Landmark', 'Hiking Trail', 'Park', 'Cave', 'Viewpoint', 'General']

// ── Add form ──────────────────────────────────────────────────────────────────
const showAdd  = ref(false)
const addForm  = useForm({ name: '', type: 'General', description: '', sitio_id: '', is_active: true })
const submitAdd = () => {
    addForm.post(route('barangay-attractions.store'), {
        preserveScroll: true,
        onSuccess: () => { addForm.reset(); showAdd.value = false }
    })
}

// ── Edit form ─────────────────────────────────────────────────────────────────
const editId   = ref(null)
const editForm = useForm({ name: '', type: 'General', description: '', sitio_id: '', is_active: true })

const startEdit = (a) => {
    editId.value            = a.id
    editForm.name           = a.name
    editForm.type           = a.type
    editForm.description    = a.description ?? ''
    editForm.sitio_id       = a.sitio_id ?? ''
    editForm.is_active      = a.is_active
}
const cancelEdit = () => { editId.value = null; editForm.reset() }

const submitEdit = (id) => {
    editForm.patch(route('barangay-attractions.update', id), {
        preserveScroll: true,
        onSuccess: () => { editId.value = null }
    })
}

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteForm = useForm({})
const deleteAttraction = (id) => {
    if (!confirm('Delete this attraction?')) return
    deleteForm.delete(route('barangay-attractions.destroy', id), { preserveScroll: true })
}

// ── Review unrecognized ───────────────────────────────────────────────────────
const reviewForm = useForm({})
const markReviewed = (id) => {
    reviewForm.patch(route('barangay-attractions.review-unrecognized', id), { preserveScroll: true })
}
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
                    <h1 class="text-lg font-semibold text-gray-800">Attraction Management</h1>
                    <p class="text-sm text-gray-500">Manage all attractions and sitio points of interest in Barangay Bel-is.</p>
                </div>
            </div>
        </div>

        <!-- Nav Tabs -->
        <div class="border-b border-gray-300 flex justify-center gap-6">
            <Link :href="route('sitios')"              :class="navClass('sitios')">Sitio List</Link>
            <Link :href="route('barangay-attractions')" :class="navClass('barangay-attractions')">Attraction Management</Link>
        </div>

        <!-- Sub-tabs: List | Notifications -->
        <div class="max-w-4xl mx-auto mt-6">
            <div class="flex gap-1 bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
                <button type="button" @click="activeTab = 'list'"
                    :class="activeTab === 'list' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                    class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all">
                    Attraction List
                </button>
                <button type="button" @click="activeTab = 'notifications'"
                    :class="activeTab === 'notifications' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                    class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all relative">
                    New Discoveries
                    <!-- Notification badge -->
                    <span v-if="unreviewedCount > 0"
                        class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                        {{ unreviewedCount }}
                    </span>
                </button>
            </div>
        </div>

        <!-- ════ LIST TAB ════ -->
        <div v-if="activeTab === 'list'" class="max-w-4xl mx-auto mt-6 space-y-6">

            <!-- Add form card -->
            <div v-if="can('edit_system_settings')" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-semibold text-gray-800">Add New Attraction</p>
                    <button v-if="!showAdd" @click="showAdd = true" type="button"
                        class="border border-blue-500 text-blue-500 text-sm font-semibold px-4 py-1.5 rounded-xl hover:bg-blue-500 hover:text-white transition">
                        + Add
                    </button>
                    <button v-else @click="showAdd = false" type="button"
                        class="border border-gray-400 text-gray-500 text-sm font-semibold px-4 py-1.5 rounded-xl hover:bg-gray-100 transition">
                        Cancel
                    </button>
                </div>

                <form v-if="showAdd" @submit.prevent="submitAdd" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-xs font-semibold mb-1">Attraction Name *</label>
                        <input v-model="addForm.name" type="text" placeholder="e.g. Hinugtan Beach"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" />
                        <p v-if="addForm.errors.name" class="text-red-500 text-xs mt-1">{{ addForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-semibold mb-1">Type *</label>
                        <select v-model="addForm.type"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option v-for="t in typeOptions" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-semibold mb-1">Sitio Location</label>
                        <select v-model="addForm.sitio_id"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">— No specific sitio —</option>
                            <option v-for="s in sitios" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-xs font-semibold mb-1">Status</label>
                        <select v-model="addForm.is_active"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-gray-700 text-xs font-semibold mb-1">Description (optional)</label>
                        <textarea v-model="addForm.description" rows="2"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                            placeholder="Brief description..."></textarea>
                    </div>
                    <div class="col-span-2 flex justify-end">
                        <button type="submit" :disabled="addForm.processing"
                            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50">
                            {{ addForm.processing ? 'Saving...' : 'Save Attraction' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Attractions table -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-800">All Attractions
                        <span class="ml-2 text-xs text-gray-400 font-normal">({{ attractions.length }})</span>
                    </p>
                </div>

                <div v-if="attractions.length === 0" class="px-6 py-10 text-center text-gray-400 text-sm">
                    No attractions added yet.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm text-left min-w-[640px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="p-3 font-semibold text-gray-700">Name</th>
                                <th class="p-3 font-semibold text-gray-700">Type</th>
                                <th class="p-3 font-semibold text-gray-700">Sitio</th>
                                <th class="p-3 font-semibold text-gray-700">Status</th>
                                <th v-if="can('edit_system_settings')" class="p-3 font-semibold text-gray-700 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in attractions" :key="a.id"
                                class="border-b border-gray-100 last:border-0 hover:bg-gray-50">

                                <!-- View mode -->
                                <template v-if="editId !== a.id">
                                    <td class="p-3 font-medium text-gray-800">{{ a.name }}</td>
                                    <td class="p-3 text-gray-500">{{ a.type }}</td>
                                    <td class="p-3 text-gray-500">{{ a.sitio_name }}</td>
                                    <td class="p-3">
                                        <span :class="a.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                            class="text-xs font-semibold px-2 py-0.5 rounded-full">
                                            {{ a.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td v-if="can('edit_system_settings')" class="p-3 text-right space-x-2">
                                        <button @click="startEdit(a)" class="text-xs font-semibold text-blue-600 hover:underline">Edit</button>
                                        <button @click="deleteAttraction(a.id)" class="text-xs font-semibold text-red-500 hover:underline">Delete</button>
                                    </td>
                                </template>

                                <!-- Edit mode (inline) -->
                                <template v-else>
                                    <td class="p-2">
                                        <input v-model="editForm.name" type="text"
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none" />
                                    </td>
                                    <td class="p-2">
                                        <select v-model="editForm.type"
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none">
                                            <option v-for="t in typeOptions" :key="t" :value="t">{{ t }}</option>
                                        </select>
                                    </td>
                                    <td class="p-2">
                                        <select v-model="editForm.sitio_id"
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none">
                                            <option value="">— None —</option>
                                            <option v-for="s in sitios" :key="s.id" :value="s.id">{{ s.name }}</option>
                                        </select>
                                    </td>
                                    <td class="p-2">
                                        <select v-model="editForm.is_active"
                                            class="border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none">
                                            <option :value="true">Active</option>
                                            <option :value="false">Inactive</option>
                                        </select>
                                    </td>
                                    <td class="p-2 text-right space-x-2">
                                        <button @click="submitEdit(a.id)" :disabled="editForm.processing"
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
        </div>

        <!-- ════ NOTIFICATIONS TAB ════ -->
        <div v-if="activeTab === 'notifications'" class="max-w-4xl mx-auto mt-6 space-y-4">

            <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800">New Destination Reports</p>
                    <p class="text-xs text-amber-700 mt-0.5">
                        These are destinations typed by visitors that are not in your current attractions list.
                        Review each one — if it's a real place, consider adding it to the Attraction List.
                    </p>
                </div>
            </div>

            <div v-if="unreviewed.length === 0"
                class="bg-white border border-gray-200 rounded-2xl shadow-sm px-6 py-12 text-center text-gray-400 text-sm">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                </svg>
                All reports have been reviewed. You're up to date!
            </div>

            <div v-else class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="p-3 font-semibold text-gray-700">Reported Destination</th>
                            <th class="p-3 font-semibold text-gray-700">Visitor</th>
                            <th class="p-3 font-semibold text-gray-700">Registration</th>
                            <th class="p-3 font-semibold text-gray-700">Date</th>
                            <th class="p-3 font-semibold text-gray-700 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in unreviewed" :key="u.id"
                            class="border-b border-gray-100 last:border-0 hover:bg-amber-50/40">
                            <td class="p-3">
                                <span class="font-semibold text-gray-800">{{ u.name }}</span>
                                <span class="ml-2 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full font-semibold">New</span>
                            </td>
                            <td class="p-3 text-gray-600">{{ u.visitor_name || '—' }}</td>
                            <td class="p-3 font-mono text-gray-500 text-xs">{{ u.registration_id || '—' }}</td>
                            <td class="p-3 text-gray-500 text-xs">{{ u.reported_at }}</td>
                            <td class="p-3 text-right">
                                <button @click="markReviewed(u.id)" :disabled="reviewForm.processing"
                                    class="text-xs font-bold text-green-600 hover:underline disabled:opacity-50">
                                    ✓ Mark Reviewed
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </LandingLayout>
</template>