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
        <div class="relative">
          <FontAwesomeIcon
            icon="magnifying-glass"
            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"
          />
          <input
            type="text"
            placeholder="Search Settings"
            class="pl-8 pr-3 py-2 rounded-lg border-2 border-gray-200 focus:border-gray-300 focus:ring-0 text-sm"
          />
        </div>
      </div>
    </div>

    <!-- Nav Tabs -->
    <div class="border-b border-gray-300 flex justify-center gap-6">
      <Link :href="route('systemsettings')" :class="navClass('systemsettings')">General Settings</Link>
      <Link :href="route('usermanagement')" :class="navClass('usermanagement')">User Management</Link>
      <Link :href="route('auditlogs')" :class="navClass('auditlogs')">Audit Logs</Link>
      <Link :href="route('websitecontent')" :class="navClass('websitecontent')">Website Content</Link>
      <Link :href="route('virtualtour')" :class="navClass('virtualtour')">Virtual Tour</Link>
      <Link :href="route('securitysettings')" :class="navClass('securitysettings')">Security</Link>
    </div>

    <!-- Environmental Fee Management Card -->
    <div class="max-w-2xl mx-auto mt-10">
      <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">

        <!-- Card Header -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-sm font-semibold text-gray-800">Environmental Fee Management</p>
            <p class="text-sm text-gray-500">Manage fees based on tourist category.</p>
          </div>
          <button
            v-if="!isEditing"
            @click="startEditing"
            type="button"
            class="border border-blue-500 text-blue-500 text-sm font-semibold px-5 py-2 rounded-xl hover:bg-blue-500 hover:text-white transition-all duration-200"
          >
            Edit
          </button>
          <button
            v-else
            @click="cancelEditing"
            type="button"
            class="border border-gray-400 text-gray-500 text-sm font-semibold px-5 py-2 rounded-xl hover:bg-gray-100 transition-all duration-200"
          >
            Cancel
          </button>
        </div>

        <!-- Validation Errors -->
        <div
          v-if="form.errors && Object.keys(form.errors).length"
          class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-600"
        >
          <p class="font-semibold mb-1">Please fix the following:</p>
          <ul class="list-disc list-inside space-y-0.5">
            <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
          </ul>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200">
          <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th v-if="isEditing" class="p-3 w-10">
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
              <!-- Existing / Editable Rows -->
              <tr
                v-for="row in editableRows"
                :key="row._key"
                :class="[
                  'border-b border-gray-100 last:border-0 transition-colors duration-150',
                  isEditing && selectedRows.includes(row._key)
                    ? 'bg-red-50'
                    : 'hover:bg-gray-50',
                ]"
              >
                <td v-if="isEditing" class="p-3">
                  <input
                    type="checkbox"
                    :value="row._key"
                    v-model="selectedRows"
                    class="rounded border-gray-300 text-red-500 focus:ring-red-400 cursor-pointer"
                  />
                </td>
                <td class="p-3">
                  <input
                    v-if="isEditing"
                    v-model="row.category"
                    type="text"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                    placeholder="Category name"
                  />
                  <span v-else class="text-gray-700">{{ row.category }}</span>
                </td>
                <td class="p-3">
                  <input
                    v-if="isEditing"
                    v-model="row.age_range"
                    type="text"
                    class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                    placeholder="e.g. 0 – 12"
                  />
                  <span v-else class="text-gray-700">{{ row.age_range }}</span>
                </td>
                <td class="p-3">
                  <div v-if="isEditing" class="flex items-center gap-1">
                    <span class="text-gray-400 text-xs">₱</span>
                    <input
                      v-model.number="row.fee"
                      type="number"
                      min="0"
                      class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                      placeholder="0"
                    />
                  </div>
                  <span v-else class="text-gray-700">{{ row.fee }}</span>
                </td>
              </tr>

              <!-- Inline New Row Form -->
              <tr v-if="isAddingNew" class="border-t-2 border-blue-100 bg-blue-50/40">
                <td v-if="isEditing" class="p-3"><div class="w-4"></div></td>
                <td class="p-3">
                  <input
                    v-model="newRow.category"
                    ref="newCategoryInput"
                    type="text"
                    class="w-full border border-blue-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Category name"
                    @keydown.enter="confirmAddRow"
                    @keydown.escape="cancelAddRow"
                  />
                </td>
                <td class="p-3">
                  <input
                    v-model="newRow.age_range"
                    type="text"
                    class="w-full border border-blue-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="e.g. 0 – 12"
                    @keydown.enter="confirmAddRow"
                    @keydown.escape="cancelAddRow"
                  />
                </td>
                <td class="p-3">
                  <div class="flex items-center gap-1">
                    <span class="text-gray-400 text-xs">₱</span>
                    <input
                      v-model.number="newRow.fee"
                      type="number"
                      min="0"
                      class="w-full border border-blue-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                      placeholder="0"
                      @keydown.enter="confirmAddRow"
                      @keydown.escape="cancelAddRow"
                    />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Add New Category -->
        <div class="mt-3">
          <div v-if="!isAddingNew">
            <button
              @click="startAddRow"
              type="button"
              class="flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-150"
            >
              <span class="text-lg leading-none">⊕</span>
              Add New Category
            </button>
          </div>
          <div v-else class="flex items-center gap-3">
            <button
              @click="confirmAddRow"
              type="button"
              class="flex items-center gap-1.5 text-sm text-green-600 hover:text-green-800 font-medium"
            >
              <span>✓</span> Confirm
            </button>
            <button
              @click="cancelAddRow"
              type="button"
              class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 font-medium"
            >
              <span>✕</span> Cancel
            </button>
          </div>
        </div>

        <!-- Timestamp -->
        <p class="text-xs text-gray-400 mt-3">Last Updated: {{ lastUpdated }}</p>

        <!-- Action Buttons Row -->
        <div class="flex items-center justify-between mt-4">
          <!-- Delete Selected -->
          <button
            v-if="isEditing && selectedRows.length > 0"
            @click="deleteSelected"
            type="button"
            class="flex items-center gap-2 border border-red-400 text-red-500 text-sm font-semibold px-4 py-2 rounded-xl hover:bg-red-500 hover:text-white transition-all duration-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
            </svg>
            Delete Selected ({{ selectedRows.length }})
          </button>
          <div v-else></div>

          <!-- Save Changes -->
          <button
            @click="saveChanges"
            type="button"
            :disabled="form.processing"
            class="flex items-center gap-2 bg-gray-900 text-white text-sm font-semibold px-5 py-2 rounded-xl hover:bg-gray-700 disabled:opacity-50 transition-all duration-200"
          >
            <svg
              v-if="form.processing"
              class="animate-spin h-4 w-4"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>{{ form.processing ? 'Saving...' : 'Save Changes' }}</span>
          </button>
        </div>

        <!-- Success Banner -->
        <transition name="fade">
          <div
            v-if="showSuccess"
            class="mt-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3"
          >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Changes saved successfully!
          </div>
        </transition>

      </div>
    </div>
  </LandingLayout>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import LandingLayout from '@/Layouts/SidebarLayout.vue';

// ─── Props (passed by FeeCategoryController@index via Inertia) ─────────────────
const props = defineProps({
  feeCategories: {
    type: Array,
    default: () => [],
  },
});

// ─── Nav ──────────────────────────────────────────────────────────────────────
const navClass = (routeName) => [
  'pb-2 text-sm font-semibold transition border-b-2',
  route().current(routeName) || (routeName === 'systemsettings' && route().current('settings'))
    ? 'text-gray-900 border-gray-900'
    : 'text-gray-400 border-transparent hover:text-gray-600',
];

// ─── Inertia useForm (handles CSRF, loading state, errors automatically) ──────
const form = useForm({ rows: [] });

// ─── Table State ──────────────────────────────────────────────────────────────
const isEditing    = ref(false);
const editableRows = ref(props.feeCategories.map((r) => ({ ...r, _key: r.id })));
const selectedRows = ref([]);

// Timestamp derived from DB data
const lastUpdated = ref(formatTimestampFromRows(props.feeCategories));

// Add-row state
const isAddingNew      = ref(false);
const newCategoryInput = ref(null);
const newRow           = ref({ category: '', age_range: '', fee: '' });
let   tempKeyCounter   = -1; // negative = new (not yet in DB)

// Success banner
const showSuccess = ref(false);

// ─── Watch Inertia flash message ──────────────────────────────────────────────
const page = usePage();

watch(
  () => props.feeCategories,
  (newVal) => {
    lastUpdated.value = formatTimestampFromRows(newVal);
    if (isEditing.value) {
      editableRows.value = newVal.map((r) => ({ ...r, _key: r.id }));
      selectedRows.value = [];
    }
  },
  { deep: true }
);

// ─── Timestamp Helper ─────────────────────────────────────────────────────────
function formatTimestampFromRows(rows) {
  if (!rows || rows.length === 0) return 'No updates yet';
  const latest = rows.reduce((a, b) =>
    new Date(a.updated_at) > new Date(b.updated_at) ? a : b
  );
  const d    = new Date(latest.updated_at);
  const date = d.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
  const time = d.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });
  return `${date} • ${time} • ${latest.updated_by ?? 'Admin'}`;
}

// ─── Edit Mode ────────────────────────────────────────────────────────────────
function startEditing() {
  editableRows.value = props.feeCategories.map((r) => ({ ...r, _key: r.id }));
  selectedRows.value = [];
  isEditing.value    = true;
}

function cancelEditing() {
  isEditing.value    = false;
  isAddingNew.value  = false;
  selectedRows.value = [];
  newRow.value       = { category: '', age_range: '', fee: '' };
  form.clearErrors();
}

// ─── Select All ───────────────────────────────────────────────────────────────
const allSelected = computed(
  () =>
    editableRows.value.length > 0 &&
    selectedRows.value.length === editableRows.value.length
);

function toggleSelectAll(e) {
  selectedRows.value = e.target.checked
    ? editableRows.value.map((r) => r._key)
    : [];
}

// ─── Delete Selected ──────────────────────────────────────────────────────────
function deleteSelected() {
  editableRows.value = editableRows.value.filter(
    (r) => !selectedRows.value.includes(r._key)
  );
  selectedRows.value = [];
}

// ─── Add New Row ──────────────────────────────────────────────────────────────
function startAddRow() {
  newRow.value      = { category: '', age_range: '', fee: '' };
  isAddingNew.value = true;
  nextTick(() => newCategoryInput.value?.focus());
}

function confirmAddRow() {
  const cat = newRow.value.category.trim();
  if (!cat) return;
  editableRows.value.push({
    id:        null,              // null tells Laravel to INSERT, not UPDATE
    _key:      tempKeyCounter--, // unique local key for v-for
    category:  cat,
    age_range: newRow.value.age_range.trim() || '—',
    fee:       newRow.value.fee === '' ? 0 : Number(newRow.value.fee),
  });
  isAddingNew.value = false;
  newRow.value      = { category: '', age_range: '', fee: '' };
}

function cancelAddRow() {
  isAddingNew.value = false;
  newRow.value      = { category: '', age_range: '', fee: '' };
}

// ─── Save → POST to Laravel ───────────────────────────────────────────────────
function saveChanges() {
  // Auto-confirm a pending new row if it has content
  if (isAddingNew.value && newRow.value.category.trim()) confirmAddRow();
  else if (isAddingNew.value) cancelAddRow();

  // Build payload (id = null for new rows so Laravel does INSERT via updateOrCreate)
  form.rows = editableRows.value.map((r) => ({
    id:        r.id ?? null,
    category:  r.category,
    age_range: r.age_range,
    fee:       r.fee,
  }));

  form.post(route('fee-categories.update'), {
    preserveScroll: true,
    onSuccess: () => {
      isEditing.value    = false;
      selectedRows.value = [];
    },
  });
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

tbody tr {
  transition: background-color 0.15s ease;
}

input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type='number'] {
  -moz-appearance: textfield;
}
</style>