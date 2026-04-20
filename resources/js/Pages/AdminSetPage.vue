<template>
  <LandingLayout>
    <!-- Top Search Bar -->
    <div class="container mx-auto">
      <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
        <div class="relative flex-1">
          <input
            type="text"
            placeholder="Search..."
            class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0"
          />
        </div>
        <FontAwesomeIcon icon="bell" />
        <FontAwesomeIcon icon="user" />
      </div>
    </div>

    <!-- Page Header -->
    <div class="p-4 mt-4 rounded-lg flex flex-col">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h1 class="text-lg font-semibold text-gray-800">System Settings</h1>
          <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
        </div>
      </div>
    </div>

    <!-- Nav Tabs (Permission Protected) -->
    <div class="border-b border-gray-300 flex justify-center gap-6">
      <Link v-if="can('manage system')" :href="route('settings')" :class="navClass('settings')">General Settings</Link>
      <Link v-if="can('view users')" :href="route('usermanagement')" :class="navClass('usermanagement')">User Management</Link>
      <Link v-if="can('view audit logs')" :href="route('auditlogs')" :class="navClass('auditlogs')">Audit Logs</Link>
      <Link v-if="can('view content')" :href="route('websitecontent')" :class="navClass('websitecontent')">Website Content</Link>
      <Link v-if="can('view virtual tour')" :href="route('virtualtour')" :class="navClass('virtualtour')">Virtual Tour</Link>
      <Link v-if="can('manage settings')" :href="route('securitysettings')" :class="navClass('securitysettings')">Security</Link>
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
            v-if="!isEditing && can('manage system')"
            @click="isEditing = true"
            type="button"
            class="border border-blue-500 text-blue-500 text-sm font-semibold px-5 py-2 rounded-xl hover:bg-blue-500 hover:text-white transition"
          >
            Edit
          </button>
          <button
            v-else-if="isEditing"
            @click="isEditing = false"
            type="button"
            class="border border-gray-400 text-gray-500 text-sm font-semibold px-5 py-2 rounded-xl hover:bg-gray-100 transition"
          >
            Cancel
          </button>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200">
          <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th v-if="isEditing" class="p-3 w-10"></th>
                <th class="p-3 font-semibold text-gray-700">Category</th>
                <th class="p-3 font-semibold text-gray-700">Age Range</th>
                <th class="p-3 font-semibold text-gray-700">Fee (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in editableRows" :key="row.id" class="border-b border-gray-100 last:border-0">
                <td v-if="isEditing" class="p-3">
                  <input type="checkbox" :value="row.id" v-model="selectedRows" class="rounded border-gray-300 text-red-500">
                </td>
                <td class="p-3">
                  <input v-if="isEditing" v-model="row.category" type="text" class="w-full border border-gray-300 rounded px-2 py-1">
                  <span v-else>{{ row.category }}</span>
                </td>
                <td class="p-3">
                  <input v-if="isEditing" v-model="row.age_range" type="text" class="w-full border border-gray-300 rounded px-2 py-1">
                  <span v-else>{{ row.age_range }}</span>
                </td>
                <td class="p-3">
                  <input v-if="isEditing" v-model.number="row.fee" type="number" class="w-full border border-gray-300 rounded px-2 py-1">
                  <span v-else>{{ row.fee }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex items-center justify-end mt-4">
          <button
            v-if="can('manage system')"
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
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import LandingLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({
  feeCategories: Array
});

// 1. Initialize Form and State
const isEditing = ref(false);
const selectedRows = ref([]);
const editableRows = ref(JSON.parse(JSON.stringify(props.feeCategories || [])));
const form = useForm({ rows: [] });

// 2. Permission Helper
const page = usePage();
const permissions = computed(() => page.props.auth.permissions || []);
const userRole = computed(() => page.props.auth.user.role || '');

const can = (permission) => {
    if (userRole.value.toLowerCase() === 'admin' || userRole.value.toLowerCase() === 'system admin') return true;
    return permissions.value.includes(permission);
};

// 3. Sync local state when props change
watch(() => props.feeCategories, (newVal) => {
    editableRows.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

// 4. Save Changes logic
const saveChanges = () => {
  form.rows = editableRows.value;
  form.post(route('fee-categories.update'), {
    preserveScroll: true,
    onSuccess: () => {
      isEditing.value = false;
      alert('Fees updated successfully!');
    },
  });
};

// 5. Navigation helper
const navClass = (routeName) => [
  'pb-2 text-sm font-semibold transition border-b-2',
  route().current(routeName)
    ? 'text-gray-900 border-gray-900'
    : 'text-gray-400 border-transparent hover:text-gray-600',
];
</script>