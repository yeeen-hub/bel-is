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

    <!-- Nav Tabs (UNCHANGED) -->
    <div class="border-b border-gray-300 flex justify-center gap-6">
      <Link v-if="can('view_system_settings')" :href="route('settings')" :class="navClass('settings')">General Settings</Link>
      <Link v-if="can('view_user_management')" :href="route('usermanagement')" :class="navClass('usermanagement')">User Management</Link>
      <Link v-if="can('view_audit_logs')" :href="route('auditlogs')" :class="navClass('auditlogs')">Audit Logs</Link>
      <Link v-if="can('view_website_content')" :href="route('websitecontent')" :class="navClass('websitecontent')">Website Content</Link>
      <Link v-if="can('view_virtual_tour')" :href="route('virtualtour')" :class="navClass('virtualtour')">Virtual Tour</Link>
      <Link v-if="can('view_security')" :href="route('securitysettings')" :class="navClass('securitysettings')">Security</Link>
    </div>

    <div class="max-w-2xl mx-auto mt-10 space-y-8 pb-16">

      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <!-- CARD 1 : Environmental Fee Management (UNCHANGED)                  -->
      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-sm font-semibold text-gray-800">Environmental Fee Management</p>
            <p class="text-sm text-gray-500">Manage fees based on tourist category.</p>
          </div>
          <button v-if="!feeEditing" @click="startFeeEdit" type="button"
            :class="['text-sm font-semibold px-5 py-2 rounded-xl transition-all',
              !can('edit_system_settings') ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
              : 'border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white']"
            :disabled="!can('edit_system_settings')">Edit</button>
          <button v-else @click="cancelFeeEdit" type="button"
            class="border border-gray-400 text-gray-500 text-sm font-semibold px-5 py-2 rounded-xl hover:bg-gray-100 transition">Cancel</button>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200">
          <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th v-if="feeEditing" class="p-3 w-10">
                  <input type="checkbox" :checked="feeAllSelected" @change="feeToggleAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                </th>
                <th class="p-3 font-semibold text-gray-700">Category</th>
                <th class="p-3 font-semibold text-gray-700">Age Range</th>
                <th class="p-3 font-semibold text-gray-700">Fee (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in feeRows" :key="row._key"
                :class="['border-b border-gray-100 last:border-0 hover:bg-gray-50 transition',
                  feeSelected.includes(row._key) ? 'bg-red-50' : '']">
                <td v-if="feeEditing" class="p-3">
                  <input type="checkbox" :value="row._key" v-model="feeSelected"
                    class="rounded border-gray-300 text-red-500 focus:ring-red-400">
                </td>
                <td class="p-3">
                  <input v-if="feeEditing" v-model="row.category" type="text"
                    class="w-full border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <span v-else class="text-gray-700">{{ row.category }}</span>
                </td>
                <td class="p-3">
                  <input v-if="feeEditing" v-model="row.age_range" type="text"
                    class="w-full border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <span v-else class="text-gray-700">{{ row.age_range }}</span>
                </td>
                <td class="p-3">
                  <input v-if="feeEditing" v-model.number="row.fee" type="number"
                    class="w-full border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <span v-else class="text-gray-700">{{ row.fee }}</span>
                </td>
              </tr>
              <tr v-if="feeAddingNew" class="bg-blue-50/50">
                <td v-if="feeEditing" class="p-3"></td>
                <td class="p-3"><input v-model="feeNewRow.category" type="text" placeholder="New Category"
                  class="w-full border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none"></td>
                <td class="p-3"><input v-model="feeNewRow.age_range" type="text" placeholder="Age Range"
                  class="w-full border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none"></td>
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <input v-model.number="feeNewRow.fee" type="number" placeholder="0"
                      class="w-full border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <button @click="feeConfirmAdd" class="text-green-600 font-bold">✓</button>
                    <button @click="feeAddingNew = false" class="text-red-600 font-bold">✕</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="feeEditing" class="mt-3">
          <button @click="feeStartAdd" type="button"
            class="flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium transition">
            <span class="text-lg">⊕</span> Add New Category
          </button>
        </div>

        <div class="flex items-center justify-between mt-6">
          <button v-if="feeEditing && feeSelected.length > 0" @click="feeDeleteSelected" type="button"
            class="flex items-center gap-2 border border-red-400 text-red-500 text-sm font-semibold px-4 py-2 rounded-xl hover:bg-red-500 hover:text-white transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
            </svg>
            Delete Selected ({{ feeSelected.length }})
          </button>
          <div v-else></div>
          <button v-if="feeEditing && can('edit_system_settings')" @click="saveFee" :disabled="feeForm.processing"
            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50">
            {{ feeForm.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <!-- CARD 2 : Sitio Management                                          -->
      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-sm font-semibold text-gray-800">Sitio Management</p>
            <p class="text-sm text-gray-500">Manage the list of sitios in Barangay Bel-is.</p>
          </div>
          <button v-if="!sitioEditing" @click="startSitioEdit" type="button"
            :class="['text-sm font-semibold px-5 py-2 rounded-xl transition-all',
              !can('edit_system_settings') ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
              : 'border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white']"
            :disabled="!can('edit_system_settings')">Edit</button>
          <button v-else @click="cancelSitioEdit" type="button"
            class="border border-gray-400 text-gray-500 text-sm font-semibold px-5 py-2 rounded-xl hover:bg-gray-100 transition">Cancel</button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
          <table class="w-full text-sm text-left min-w-[480px]">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th v-if="sitioEditing" class="p-3 w-10">
                  <input type="checkbox" :checked="sitioAllSelected" @change="sitioToggleAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                </th>
                <th class="p-3 font-semibold text-gray-700">Sitio Name</th>
                <th class="p-3 font-semibold text-gray-700">Description</th>
                <!-- Fixed-width Status column so the select never overflows -->
                <th class="p-3 font-semibold text-gray-700 w-28">Status</th>
                <!-- Action column only visible on new-row -->
                <th v-if="sitioEditing" class="p-3 w-16"></th>
              </tr>
            </thead>
            <tbody>
              <!-- Existing rows -->
              <tr v-for="row in sitioRows" :key="row._key"
                :class="['border-b border-gray-100 last:border-0 hover:bg-gray-50 transition',
                  sitioSelected.includes(row._key) ? 'bg-red-50' : '']">
                <td v-if="sitioEditing" class="p-3">
                  <input type="checkbox" :value="row._key" v-model="sitioSelected"
                    class="rounded border-gray-300 text-red-500 focus:ring-red-400">
                </td>
                <td class="p-3">
                  <input v-if="sitioEditing" v-model="row.name" type="text"
                    class="w-full border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <span v-else class="text-gray-700 font-medium">{{ row.name }}</span>
                </td>
                <td class="p-3">
                  <input v-if="sitioEditing" v-model="row.description" type="text" placeholder="Optional description"
                    class="w-full border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <span v-else class="text-gray-500">{{ row.description || '—' }}</span>
                </td>
                <td class="p-3">
                  <select v-if="sitioEditing" v-model="row.is_active"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                  </select>
                  <span v-else :class="row.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                    class="text-xs font-semibold px-2 py-0.5 rounded-full">
                    {{ row.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <!-- Empty actions cell to keep column alignment -->
                <td v-if="sitioEditing" class="p-3"></td>
              </tr>
              <!-- New-row — Status and action buttons each in their own <td> -->
              <tr v-if="sitioAddingNew" class="bg-blue-50/50 border-b border-gray-100">
                <td v-if="sitioEditing" class="p-3"></td>
                <td class="p-3">
                  <input v-model="sitioNewRow.name" type="text" placeholder="Sitio Name"
                    class="w-full border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </td>
                <td class="p-3">
                  <input v-model="sitioNewRow.description" type="text" placeholder="Description (optional)"
                    class="w-full border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </td>
                <!-- Status select — no buttons crammed in here -->
                <td class="p-3">
                  <select v-model="sitioNewRow.is_active"
                    class="w-full border border-blue-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                  </select>
                </td>
                <!-- Confirm / cancel in their own dedicated cell -->
                <td class="p-3 whitespace-nowrap">
                  <button @click="sitioConfirmAdd"
                    class="text-green-600 hover:text-green-800 font-bold text-base mr-2">✓</button>
                  <button @click="sitioAddingNew = false"
                    class="text-red-500 hover:text-red-700 font-bold text-base">✕</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="sitioEditing" class="mt-3">
          <button @click="sitioStartAdd" type="button"
            class="flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium transition">
            <span class="text-lg">⊕</span> Add New Sitio
          </button>
        </div>

        <div class="flex items-center justify-between mt-6">
          <button v-if="sitioEditing && sitioSelected.length > 0" @click="sitioDeleteSelected" type="button"
            class="flex items-center gap-2 border border-red-400 text-red-500 text-sm font-semibold px-4 py-2 rounded-xl hover:bg-red-500 hover:text-white transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
            </svg>
            Delete Selected ({{ sitioSelected.length }})
          </button>
          <div v-else></div>
          <button v-if="sitioEditing && can('edit_system_settings')" @click="saveSitios" :disabled="sitioForm.processing"
            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50">
            {{ sitioForm.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <!-- CARD 3 : Attraction Management                                     -->
      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-sm font-semibold text-gray-800">
              Attraction Management
              <span v-if="unreviewedCount > 0"
                class="ml-2 inline-flex items-center justify-center w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full">
                {{ unreviewedCount }}
              </span>
            </p>
            <p class="text-sm text-gray-500">Manage all attractions and points of interest in Barangay Bel-is.</p>
          </div>
          <button v-if="!attrEditing" @click="startAttrEdit" type="button"
            :class="['text-sm font-semibold px-5 py-2 rounded-xl transition-all',
              !can('edit_system_settings') ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
              : 'border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white']"
            :disabled="!can('edit_system_settings')">Edit</button>
          <button v-else @click="cancelAttrEdit" type="button"
            class="border border-gray-400 text-gray-500 text-sm font-semibold px-5 py-2 rounded-xl hover:bg-gray-100 transition">Cancel</button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
          <table class="w-full text-sm text-left min-w-[620px]">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th v-if="attrEditing" class="p-3 w-10">
                  <input type="checkbox" :checked="attrAllSelected" @change="attrToggleAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                </th>
                <th class="p-3 font-semibold text-gray-700">Name</th>
                <!-- Fixed widths prevent Type/Sitio/Status from squishing each other -->
                <th class="p-3 font-semibold text-gray-700 w-32">Type</th>
                <th class="p-3 font-semibold text-gray-700 w-36">Sitio</th>
                <th class="p-3 font-semibold text-gray-700 w-28">Status</th>
                <!-- Dedicated action column for new-row confirm/cancel -->
                <th v-if="attrEditing" class="p-3 w-16"></th>
              </tr>
            </thead>
            <tbody>
              <!-- Existing rows -->
              <tr v-for="row in attrRows" :key="row._key"
                :class="['border-b border-gray-100 last:border-0 hover:bg-gray-50 transition',
                  attrSelected.includes(row._key) ? 'bg-red-50' : '']">
                <td v-if="attrEditing" class="p-3">
                  <input type="checkbox" :value="row._key" v-model="attrSelected"
                    class="rounded border-gray-300 text-red-500 focus:ring-red-400">
                </td>
                <td class="p-3">
                  <input v-if="attrEditing" v-model="row.name" type="text"
                    class="w-full border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                  <span v-else class="text-gray-700 font-medium">{{ row.name }}</span>
                </td>
                <td class="p-3">
                  <select v-if="attrEditing" v-model="row.type"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option v-for="t in attractionTypes" :key="t" :value="t">{{ t }}</option>
                  </select>
                  <span v-else class="text-gray-500">{{ row.type }}</span>
                </td>
                <td class="p-3">
                  <select v-if="attrEditing" v-model="row.sitio_id"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="null">— None —</option>
                    <option v-for="s in sitioRows" :key="s._key" :value="Number(s.id)">{{ s.name }}</option>
                  </select>
                  <span v-else class="text-gray-500">{{ row.sitio_name || '—' }}</span>
                </td>
                <td class="p-3">
                  <select v-if="attrEditing" v-model="row.is_active"
                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                  </select>
                  <span v-else :class="row.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                    class="text-xs font-semibold px-2 py-0.5 rounded-full">
                    {{ row.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <!-- Empty actions cell for column alignment -->
                <td v-if="attrEditing" class="p-3"></td>
              </tr>
              <!-- New-row — each field in its own <td>, buttons in their own <td> -->
              <tr v-if="attrAddingNew" class="bg-blue-50/50 border-b border-gray-100">
                <td v-if="attrEditing" class="p-3"></td>
                <td class="p-3">
                  <input v-model="attrNewRow.name" type="text" placeholder="Attraction Name"
                    class="w-full border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </td>
                <td class="p-3">
                  <select v-model="attrNewRow.type"
                    class="w-full border border-blue-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option v-for="t in attractionTypes" :key="t" :value="t">{{ t }}</option>
                  </select>
                </td>
                <!-- Sitio select — no buttons crammed in here -->
                <td class="p-3">
                  <select v-model="attrNewRow.sitio_id"
                    class="w-full border border-blue-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="null">— None —</option>
                    <option v-for="s in sitioRows" :key="s._key" :value="Number(s.id)">{{ s.name }}</option>
                  </select>
                </td>
                <!-- Status select — standalone, no buttons -->
                <td class="p-3">
                  <select v-model="attrNewRow.is_active"
                    class="w-full border border-blue-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                  </select>
                </td>
                <!-- Confirm / cancel in their own dedicated cell -->
                <td class="p-3 whitespace-nowrap">
                  <button @click="attrConfirmAdd"
                    class="text-green-600 hover:text-green-800 font-bold text-base mr-2">✓</button>
                  <button @click="attrAddingNew = false"
                    class="text-red-500 hover:text-red-700 font-bold text-base">✕</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="attrEditing" class="mt-3">
          <button @click="attrStartAdd" type="button"
            class="flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium transition">
            <span class="text-lg">⊕</span> Add New Attraction
          </button>
        </div>

        <div class="flex items-center justify-between mt-6">
          <button v-if="attrEditing && attrSelected.length > 0" @click="attrDeleteSelected" type="button"
            class="flex items-center gap-2 border border-red-400 text-red-500 text-sm font-semibold px-4 py-2 rounded-xl hover:bg-red-500 hover:text-white transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
            </svg>
            Delete Selected ({{ attrSelected.length }})
          </button>
          <div v-else></div>
          <button v-if="attrEditing && can('edit_system_settings')" @click="saveAttractions" :disabled="attrForm.processing"
            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50">
            {{ attrForm.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <!-- CARD 4 : New Destination Discoveries                               -->
      <!-- Each row has two actions:                                          -->
      <!--   "Add to Attractions" — expands an inline mini-form pre-filled    -->
      <!--     with the reported name; staff picks type + sitio then saves.   -->
      <!--     On success the attraction is created AND the report is marked   -->
      <!--     reviewed in one request.                                        -->
      <!--   "Mark Reviewed" — dismisses without adding (already exists, etc) -->
      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <div v-if="unreviewed.length > 0" class="bg-white border border-amber-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
          <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
          </svg>
          <div>
            <p class="text-sm font-semibold text-gray-800">
              New Destination Discoveries
              <span class="ml-2 inline-flex items-center justify-center w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full">
                {{ unreviewed.length }}
              </span>
            </p>
            <p class="text-xs text-gray-500 mt-0.5">
              Visitors typed destinations not in your list. Click <strong>Add to Attractions</strong> to officially list it, or <strong>Mark Reviewed</strong> to dismiss.
            </p>
          </div>
        </div>

        <div class="space-y-3">
          <div v-for="u in unreviewed" :key="u.id"
            class="border border-amber-100 rounded-xl overflow-hidden">

            <!-- Row summary -->
            <div class="flex items-center justify-between px-4 py-3 bg-amber-50/60 hover:bg-amber-50 transition">
              <div class="flex items-center gap-3 min-w-0">
                <!-- Reported name + badge -->
                <div class="min-w-0">
                  <span class="font-semibold text-gray-800 text-sm">{{ u.name }}</span>
                  <span class="ml-2 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full font-semibold align-middle">New</span>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Reported by
                    <span class="text-gray-600 font-medium">{{ u.visitor_name || 'Unknown' }}</span>
                    <span v-if="u.registration_id" class="font-mono ml-1">({{ u.registration_id }})</span>
                    · {{ u.reported_at }}
                  </p>
                </div>
              </div>

              <!-- Action buttons -->
              <div class="flex items-center gap-2 ml-4 shrink-0">
                <!-- Toggle "Add to Attractions" inline form -->
                <button
                  v-if="expandedAdd !== u.id"
                  @click="openAddForm(u)"
                  type="button"
                  class="text-xs font-bold px-3 py-1.5 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-500 hover:text-white transition">
                  + Add to Attractions
                </button>
                <button
                  v-else
                  @click="closeAddForm"
                  type="button"
                  class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-100 transition">
                  Cancel
                </button>

                <!-- Mark reviewed only (dismiss without adding) -->
                <button
                  @click="markReviewed(u.id)"
                  :disabled="reviewForm.processing"
                  type="button"
                  class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition disabled:opacity-50">
                  ✓ Dismiss
                </button>
              </div>
            </div>

            <!-- Inline "Add to Attractions" form — expands below the row -->
            <div v-if="expandedAdd === u.id"
              class="px-4 py-4 bg-white border-t border-amber-100">
              <p class="text-xs font-semibold text-gray-600 mb-3">
                Add <span class="text-blue-600">"{{ u.name }}"</span> to the official Attractions list:
              </p>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <!-- Name (pre-filled, editable) -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Attraction Name</label>
                  <input v-model="addAttrForm.name" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
                  <p v-if="addAttrForm.errors.name" class="text-red-500 text-xs mt-1">{{ addAttrForm.errors.name }}</p>
                </div>

                <!-- Type -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Type</label>
                  <select v-model="addAttrForm.type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option v-for="t in attractionTypes" :key="t" :value="t">{{ t }}</option>
                  </select>
                </div>

                <!-- Sitio -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Sitio (optional)</label>
                  <select v-model="addAttrForm.sitio_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="null">— None —</option>
                    <option v-for="s in sitioRows" :key="s._key" :value="Number(s.id)">{{ s.name }}</option>
                  </select>
                </div>
              </div>

              <div class="flex items-center justify-between mt-4">
                <p class="text-xs text-gray-400">
                  This will add the attraction to the official list and mark this report as reviewed.
                </p>
                <button
                  @click="submitAddFromUnrecognized(u.id)"
                  :disabled="addAttrForm.processing || !addAttrForm.name.trim()"
                  type="button"
                  class="bg-gray-900 text-white text-xs font-bold px-5 py-2 rounded-xl hover:bg-black transition disabled:opacity-50 ml-4 shrink-0">
                  {{ addAttrForm.processing ? 'Adding...' : '✓ Add to Attractions' }}
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </LandingLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/SidebarLayout.vue';

const props = defineProps({
  feeCategories:        { type: Array, default: () => [] },
  sitios:               { type: Array, default: () => [] },
  barangayAttractions:  { type: Array, default: () => [] },
  unreviewedCount:      { type: Number, default: 0 },
  unreviewed:           { type: Array,  default: () => [] },
});

const page        = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const userRole    = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase());
const can = (p) => userRole.value === 'admin' || permissions.value.includes(p);

const attractionTypes = ['Resort', 'Beach', 'Falls', 'Landmark', 'Hiking Trail', 'Park', 'Cave', 'Viewpoint', 'General'];

// ─────────────────────────────────────────────────────────────────────────────
// Stable unique _key per row.
// Persisted rows: "db-{id}"  — guaranteed unique since DB IDs are unique.
// New (unsaved) rows: "new-{n}" — counter-based, never collides with DB keys.
// Using _key (not id) everywhere for v-for :key and checkbox v-model tracking
// eliminates the type-mismatch bug (string ID vs number ID).
// ─────────────────────────────────────────────────────────────────────────────
let tempCounter = 0;
const newTempKey = () => `new-${++tempCounter}`;

// Mapper functions — run once per row on initialisation and on prop refresh.
// toAttrRow casts sitio_id to Number (or null) so the <select> :value="Number(s.id)"
// comparison works with strict equality (Vue's v-model uses ===).
const toFeeRow   = (r) => ({ ...r,  _key: `db-${r.id}` });
const toSitioRow = (r) => ({ ...r,  _key: `db-${r.id}`, is_active: Boolean(r.is_active) });
const toAttrRow  = (r) => ({
  ...r,
  _key:      `db-${r.id}`,
  sitio_id:  r.sitio_id != null ? Number(r.sitio_id) : null,   // ← FIX: cast to Number
  is_active: Boolean(r.is_active),
});

// ─────────────────────────────────────────────────────────────────────────────
// CARD 1 — Fee Categories
// ─────────────────────────────────────────────────────────────────────────────
const feeEditing   = ref(false);
const feeAddingNew = ref(false);
const feeSelected  = ref([]);
const feeRows      = ref(props.feeCategories.map(toFeeRow));
const feeNewRow    = ref({ category: '', age_range: '', fee: '' });
const feeForm      = useForm({ rows: [] });

// FIX: Only sync from fresh props when NOT currently editing.
// Previously the watch always fired after save (Inertia pushes fresh props),
// which reset the rows back — making the table look empty until page reload.
// Now: when editing is active we leave rows alone; when idle we sync.
watch(() => props.feeCategories, (v) => {
  if (!feeEditing.value) feeRows.value = v.map(toFeeRow);
}, { deep: true });

const feeAllSelected = computed(() =>
  feeRows.value.length > 0 && feeSelected.value.length === feeRows.value.length);
function startFeeEdit()  { feeEditing.value = true; }
function cancelFeeEdit() {
  feeEditing.value = false; feeAddingNew.value = false; feeSelected.value = [];
  feeRows.value = props.feeCategories.map(toFeeRow);
}
function feeToggleAll(e)  { feeSelected.value = e.target.checked ? feeRows.value.map(r => r._key) : []; }
function feeStartAdd()    { feeAddingNew.value = true; feeNewRow.value = { category: '', age_range: '', fee: '' }; }
function feeConfirmAdd()  {
  if (!feeNewRow.value.category) return;
  feeRows.value.push({ ...feeNewRow.value, id: null, _key: newTempKey() });
  feeAddingNew.value = false;
}
function feeDeleteSelected() {
  if (!confirm(`Delete ${feeSelected.value.length} item(s)?`)) return;
  feeRows.value = feeRows.value.filter(r => !feeSelected.value.includes(r._key));
  feeSelected.value = [];
}
function saveFee() {
  if (feeAddingNew.value && feeNewRow.value.category) feeConfirmAdd();
  feeForm.rows = feeRows.value;
  feeForm.post(route('fee-categories.update'), {
    preserveScroll: true,
    onSuccess: () => { feeEditing.value = false; feeSelected.value = []; },
  });
}

// ─────────────────────────────────────────────────────────────────────────────
// CARD 2 — Sitio Management
// ─────────────────────────────────────────────────────────────────────────────
const sitioEditing   = ref(false);
const sitioAddingNew = ref(false);
const sitioSelected  = ref([]);
const sitioRows      = ref(props.sitios.map(toSitioRow));
const sitioNewRow    = ref({ name: '', description: '', is_active: true });
const sitioForm      = useForm({ rows: [] });

watch(() => props.sitios, (v) => {
  if (!sitioEditing.value) sitioRows.value = v.map(toSitioRow);
}, { deep: true });

const sitioAllSelected = computed(() =>
  sitioRows.value.length > 0 && sitioSelected.value.length === sitioRows.value.length);
function startSitioEdit()  { sitioEditing.value = true; }
function cancelSitioEdit() {
  sitioEditing.value = false; sitioAddingNew.value = false; sitioSelected.value = [];
  sitioRows.value = props.sitios.map(toSitioRow);
}
function sitioToggleAll(e) { sitioSelected.value = e.target.checked ? sitioRows.value.map(r => r._key) : []; }
function sitioStartAdd()   { sitioAddingNew.value = true; sitioNewRow.value = { name: '', description: '', is_active: true }; }
function sitioConfirmAdd() {
  if (!sitioNewRow.value.name.trim()) return;
  sitioRows.value.push({ ...sitioNewRow.value, id: null, _key: newTempKey() });
  sitioAddingNew.value = false;
}
function sitioDeleteSelected() {
  if (!confirm(`Delete ${sitioSelected.value.length} sitio(s)? Attractions linked to them will lose their sitio.`)) return;
  sitioRows.value = sitioRows.value.filter(r => !sitioSelected.value.includes(r._key));
  sitioSelected.value = [];
}
function saveSitios() {
  if (sitioAddingNew.value && sitioNewRow.value.name.trim()) sitioConfirmAdd();
  sitioForm.rows = sitioRows.value;
  sitioForm.post(route('sitios.update'), {
    preserveScroll: true,
    onSuccess: () => { sitioEditing.value = false; sitioSelected.value = []; },
  });
}

// ─────────────────────────────────────────────────────────────────────────────
// CARD 3 — Attraction Management
// ─────────────────────────────────────────────────────────────────────────────
const attrEditing   = ref(false);
const attrAddingNew = ref(false);
const attrSelected  = ref([]);
const attrRows      = ref(props.barangayAttractions.map(toAttrRow));
const attrNewRow    = ref({ name: '', type: 'General', description: '', sitio_id: null, is_active: true });
const attrForm      = useForm({ rows: [] });

watch(() => props.barangayAttractions, (v) => {
  if (!attrEditing.value) attrRows.value = v.map(toAttrRow);
}, { deep: true });

const attrAllSelected = computed(() =>
  attrRows.value.length > 0 && attrSelected.value.length === attrRows.value.length);
function startAttrEdit()  { attrEditing.value = true; }
function cancelAttrEdit() {
  attrEditing.value = false; attrAddingNew.value = false; attrSelected.value = [];
  attrRows.value = props.barangayAttractions.map(toAttrRow);
}
function attrToggleAll(e) { attrSelected.value = e.target.checked ? attrRows.value.map(r => r._key) : []; }
function attrStartAdd()   {
  attrAddingNew.value = true;
  attrNewRow.value = { name: '', type: 'General', description: '', sitio_id: null, is_active: true };
}
function attrConfirmAdd() {
  if (!attrNewRow.value.name.trim()) return;
  const sitio = sitioRows.value.find(s => Number(s.id) === attrNewRow.value.sitio_id);
  attrRows.value.push({
    ...attrNewRow.value,
    id: null,
    _key: newTempKey(),
    sitio_name: sitio?.name ?? '—',
  });
  attrAddingNew.value = false;
}
function attrDeleteSelected() {
  if (!confirm(`Delete ${attrSelected.value.length} attraction(s)?`)) return;
  attrRows.value = attrRows.value.filter(r => !attrSelected.value.includes(r._key));
  attrSelected.value = [];
}
function saveAttractions() {
  if (attrAddingNew.value && attrNewRow.value.name.trim()) attrConfirmAdd();
  attrForm.rows = attrRows.value;
  attrForm.post(route('barangay-attractions.update-all'), {
    preserveScroll: true,
    onSuccess: () => { attrEditing.value = false; attrSelected.value = []; },
  });
}

// ─────────────────────────────────────────────────────────────────────────────
// CARD 4 — New destination discoveries
// ─────────────────────────────────────────────────────────────────────────────

// Tracks which unrecognized row has its inline add-form expanded (one at a time)
const expandedAdd = ref(null);

// The inline add form — pre-filled when a row is opened
const addAttrForm = useForm({
  name:     '',
  type:     'General',
  sitio_id: null,
});

function openAddForm(u) {
  expandedAdd.value = u.id;
  // Pre-fill the name from what the visitor typed; staff can edit before saving
  addAttrForm.name     = u.name;
  addAttrForm.type     = 'General';
  addAttrForm.sitio_id = null;
  // Clear any previous errors
  addAttrForm.clearErrors();
}

function closeAddForm() {
  expandedAdd.value = null;
  addAttrForm.reset();
}

function submitAddFromUnrecognized(unreviewedId) {
  addAttrForm.post(route('fee-categories.add-from-unrecognized', unreviewedId), {
    preserveScroll: true,
    onSuccess: () => {
      expandedAdd.value = null;
      addAttrForm.reset();
    },
  });
}

// Dismiss without adding (already exists, not a real place, etc.)
const reviewForm = useForm({});
function markReviewed(id) {
  reviewForm.patch(route('fee-categories.review-unrecognized', id), { preserveScroll: true });
}

// ─────────────────────────────────────────────────────────────────────────────
// Nav helper (unchanged)
// ─────────────────────────────────────────────────────────────────────────────
const navClass = (routeName) => [
  'pb-2 text-sm font-semibold transition border-b-2',
  route().current(routeName)
    ? 'text-gray-900 border-gray-900'
    : 'text-gray-400 border-transparent hover:text-gray-600',
];
</script>