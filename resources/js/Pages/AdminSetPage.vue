<template>
  <LandingLayout>
    <!-- Header Section (UI Maintained) -->
    <div class="container mx-auto">
      <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
        <div class="relative flex-1">
          <input type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
        </div>
        <FontAwesomeIcon icon="bell" />
        <FontAwesomeIcon icon="user" />
      </div>
    </div>

    <div class="p-4 mt-4 rounded-lg flex flex-col">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h1 class="text-lg font-semibold text-gray-800">System Settings</h1>
          <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
        </div>
      </div>
    </div>

    <!-- Nav Tabs -->
    <div class="border-b border-gray-300 flex justify-center gap-6">
      <Link v-if="can('view_system_settings')" :href="route('settings')" :class="navClass('settings')">General Settings</Link>
      <Link v-if="can('view_user_management')" :href="route('usermanagement')" :class="navClass('usermanagement')">User Management</Link>
      <Link v-if="can('view_audit_logs')" :href="route('auditlogs')" :class="navClass('auditlogs')">Audit Logs</Link>
      <Link v-if="can('view_website_content')" :href="route('websitecontent')" :class="navClass('websitecontent')">Website Content</Link>
      <Link v-if="can('view_virtual_tour')" :href="route('virtualtour')" :class="navClass('virtualtour')">Virtual Tour</Link>
      <Link v-if="can('view_security')" :href="route('securitysettings')" :class="navClass('securitysettings')">Security</Link>
    </div>

    <!-- Environmental Fee Management Card -->
    <div class="max-w-2xl mx-auto mt-10">
      <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-sm font-semibold text-gray-800">Environmental Fee Management</p>
            <p class="text-sm text-gray-500">Manage fees based on tourist category.</p>
          </div>
          <button
            v-if="!isEditing"
            @click="startEditing"
            type="button"
            :class="['text-sm font-semibold px-5 py-2 rounded-xl transition-all', 
                    !can('edit_system_settings') ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white']"
            :disabled="!can('edit_system_settings')"
          >
            Edit
          </button>
          <button
            v-else
            @click="cancelEditing"
            type="button"
            class="border border-gray-400 text-gray-500 text-sm font-semibold px-5 py-2 rounded-xl hover:bg-gray-100 transition"
          >
            Cancel
          </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200">
          <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th v-if="isEditing" class="p-3 w-10">
                  <!-- Select All Checkbox -->
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                  />
                </th>
                <th class="p-3 font-semibold text-gray-700">Category</th>
                <th class="p-3 font-semibold text-gray-700">Age Range</th>
                <th class="p-3 font-semibold text-gray-700">Fee (₱)</th>
              </tr>
            </thead>
            <tbody>
              <!-- Existing Rows -->
              <tr v-for="row in editableRows" :key="row._key || row.id" 
                  :class="['border-b border-gray-100 last:border-0 hover:bg-gray-50 transition', 
                  selectedRows.includes(row._key || row.id) ? 'bg-red-50' : '']">
                <td v-if="isEditing" class="p-3">
                  <input type="checkbox" :value="row._key || row.id" v-model="selectedRows" class="rounded border-gray-300 text-red-500 focus:ring-red-400">
                </td>
                <td class="p-3">
                  <input v-if="isEditing" v-model="row.category" type="text" class="w-full border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <span v-else class="text-gray-700">{{ row.category }}</span>
                </td>
                <td class="p-3">
                  <input v-if="isEditing" v-model="row.age_range" type="text" class="w-full border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <span v-else class="text-gray-700">{{ row.age_range }}</span>
                </td>
                <td class="p-3">
                  <input v-if="isEditing" v-model.number="row.fee" type="number" class="w-full border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <span v-else class="text-gray-700">{{ row.fee }}</span>
                </td>
              </tr>

              <!-- Inline New Row Form -->
              <tr v-if="isAddingNew" class="bg-blue-50/50">
                <td v-if="isEditing" class="p-3"></td>
                <td class="p-3"><input v-model="newRow.category" type="text" class="w-full border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none" placeholder="New Category"></td>
                <td class="p-3"><input v-model="newRow.age_range" type="text" class="w-full border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none" placeholder="Age Range"></td>
                <td class="p-3">
                    <div class="flex items-center gap-2">
                        <input v-model.number="newRow.fee" type="number" class="w-full border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none" placeholder="0">
                        <button @click="confirmAddRow" class="text-green-600 font-bold">✓</button>
                        <button @click="isAddingNew = false" class="text-red-600 font-bold">✕</button>
                    </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Add Button (UI Maintained) -->
        <div v-if="isEditing" class="mt-3">
          <button
            @click="startAddRow"
            type="button"
            class="flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium transition"
          >
            <span class="text-lg">⊕</span> Add New Category
          </button>
        </div>

        <div class="flex items-center justify-between mt-6">
          <!-- Dynamic Delete Button -->
          <button
            v-if="isEditing && selectedRows.length > 0"
            @click="deleteSelected"
            type="button"
            class="flex items-center gap-2 border border-red-400 text-red-500 text-sm font-semibold px-4 py-2 rounded-xl hover:bg-red-500 hover:text-white transition-all duration-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
            </svg>
            Delete Selected ({{ selectedRows.length }})
          </button>
          <div v-else></div>

          <button
            v-if="isEditing && can('edit_system_settings')"
            @click="saveChanges"
            :disabled="form.processing"
            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>
  </LandingLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import LandingLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({ feeCategories: Array });
const page = usePage();

// RBAC
const permissions = computed(() => page.props.auth?.permissions ?? []);
const userRole = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase());
const can = (p) => userRole.value.includes('admin') || permissions.value.includes(p);

// STATE
const isEditing = ref(false);
const isAddingNew = ref(false);
const selectedRows = ref([]);
const editableRows = ref(props.feeCategories.map(r => ({ ...r, _key: r.id })));
const newRow = ref({ category: '', age_range: '', fee: '' });
const form = useForm({ rows: [] });
let tempKey = -1;

// Sync UI when props change
watch(() => props.feeCategories, (newVal) => {
    editableRows.value = newVal.map(r => ({ ...r, _key: r.id }));
}, { deep: true });

function startEditing() { isEditing.value = true; }
function cancelEditing() { 
    isEditing.value = false; 
    isAddingNew.value = false;
    selectedRows.value = [];
    editableRows.value = props.feeCategories.map(r => ({ ...r, _key: r.id }));
}

// SELECT ALL LOGIC
const allSelected = computed(() => 
    editableRows.value.length > 0 && selectedRows.value.length === editableRows.value.length
);

function toggleSelectAll(e) {
    selectedRows.value = e.target.checked ? editableRows.value.map(r => r._key || r.id) : [];
}

function startAddRow() {
    isAddingNew.value = true;
    newRow.value = { category: '', age_range: '', fee: '' };
}

function confirmAddRow() {
    if (!newRow.value.category) return;
    editableRows.value.push({ ...newRow.value, id: null, _key: tempKey-- });
    isAddingNew.value = false;
}

function deleteSelected() {
    if (confirm(`Are you sure you want to delete ${selectedRows.value.length} items?`)) {
        editableRows.value = editableRows.value.filter(r => !selectedRows.value.includes(r._key || r.id));
        selectedRows.value = [];
    }
}

const saveChanges = () => {
  if (isAddingNew.value && newRow.value.category) confirmAddRow();
  form.rows = editableRows.value;
  form.post(route('fee-categories.update'), {
    preserveScroll: true,
    onSuccess: () => { isEditing.value = false; selectedRows.value = []; },
  });
};

const navClass = (routeName) => [
  'pb-2 text-sm font-semibold transition border-b-2',
  route().current(routeName) ? 'text-gray-900 border-gray-900' : 'text-gray-400 border-transparent hover:text-gray-600',
];
</script>