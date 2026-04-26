<template>
  <LandingLayout>
    <!-- Header Section -->
    <div class="container mx-auto">
      <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">

        <div class="relative flex-1">
          <input v-model="search" type="text" placeholder="Search by name, origin, or registration ID..." :class="[
            'w-full p-2 pl-8 rounded-lg border text-sm transition-colors duration-200',
            search
              ? 'border-gray-800 bg-white ring-1 ring-gray-800'
              : 'border-gray-300 bg-white focus:border-gray-400'
          ]" />
          <svg class="absolute left-2.5 top-2.5 w-4 h-4" :class="search ? 'text-gray-800' : 'text-gray-400'" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <span v-if="search" class="absolute right-2.5 top-2 text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
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
              <span v-if="pendingFees > 0" class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">
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
                  <button @click="feeStatus = 'Pending'; showNotifications = false;"
                    class="text-xs text-yellow-600 font-semibold mt-1 inline-block hover:underline">
                    Show Pending Records →
                  </button>
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

    <div class="p-4 mt-4 rounded-lg flex flex-col">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h1 class="text-lg font-semibold text-gray-800">System Settings</h1>
          <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
        </div>
      </div>
    </div>

    <!-- Nav Tabs -->
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

    <div class="max-w-2xl mx-auto mt-10 space-y-8 pb-16">

      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <!-- CARD 1 : Environmental Fee Management                              -->
      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-sm font-semibold text-gray-800">Environmental Fee Management</p>
            <p class="text-sm text-gray-500">Manage fees based on tourist category.</p>
          </div>
          <button v-if="!feeEditing" @click="startFeeEdit" type="button" :class="['text-sm font-semibold px-5 py-2 rounded-xl transition-all',
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
              <tr v-for="row in feeRows" :key="row._key" :class="['border-b border-gray-100 last:border-0 hover:bg-gray-50 transition',
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
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
          <button v-if="!sitioEditing" @click="startSitioEdit" type="button" :class="['text-sm font-semibold px-5 py-2 rounded-xl transition-all',
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
                <th class="p-3 font-semibold text-gray-700 w-28">Status</th>
                <th v-if="sitioEditing" class="p-3 w-16"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in sitioRows" :key="row._key" :class="['border-b border-gray-100 last:border-0 hover:bg-gray-50 transition',
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
                <td v-if="sitioEditing" class="p-3"></td>
              </tr>
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
                <td class="p-3">
                  <select v-model="sitioNewRow.is_active"
                    class="w-full border border-blue-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                  </select>
                </td>
                <td class="p-3 whitespace-nowrap">
                  <button @click="sitioConfirmAdd" class="text-green-600 hover:text-green-800 font-bold text-base mr-2">✓</button>
                  <button @click="sitioAddingNew = false" class="text-red-500 hover:text-red-700 font-bold text-base">✕</button>
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
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
          <button v-if="!attrEditing" @click="startAttrEdit" type="button" :class="['text-sm font-semibold px-5 py-2 rounded-xl transition-all',
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
                <th class="p-3 font-semibold text-gray-700 w-32">Type</th>
                <th class="p-3 font-semibold text-gray-700 w-36">Sitio</th>
                <th class="p-3 font-semibold text-gray-700 w-28">Status</th>
                <th v-if="attrEditing" class="p-3 w-16"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in attrRows" :key="row._key" :class="['border-b border-gray-100 last:border-0 hover:bg-gray-50 transition',
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
                <td v-if="attrEditing" class="p-3"></td>
              </tr>
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
                <td class="p-3">
                  <select v-model="attrNewRow.sitio_id"
                    class="w-full border border-blue-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="null">— None —</option>
                    <option v-for="s in sitioRows" :key="s._key" :value="Number(s.id)">{{ s.name }}</option>
                  </select>
                </td>
                <td class="p-3">
                  <select v-model="attrNewRow.is_active"
                    class="w-full border border-blue-300 rounded px-2 py-1 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                  </select>
                </td>
                <td class="p-3 whitespace-nowrap">
                  <button @click="attrConfirmAdd" class="text-green-600 hover:text-green-800 font-bold text-base mr-2">✓</button>
                  <button @click="attrAddingNew = false" class="text-red-500 hover:text-red-700 font-bold text-base">✕</button>
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
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
      <!-- ═══════════════════════════════════════════════════════════════════ -->
      <div v-if="unreviewed.length > 0" class="bg-white border border-amber-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
          <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
              clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-semibold text-gray-800">
              New Destination Discoveries
              <span class="ml-2 inline-flex items-center justify-center w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full">
                {{ unreviewed.length }}
              </span>
            </p>
            <p class="text-xs text-gray-500 mt-0.5">
              Visitors typed destinations not in your list. Click <strong>Add to Attractions</strong> to officially list
              it, or <strong>Mark Reviewed</strong> to dismiss.
            </p>
          </div>
        </div>

        <div class="space-y-3">
          <div v-for="u in unreviewed" :key="u.id" class="border border-amber-100 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 bg-amber-50/60 hover:bg-amber-50 transition">
              <div class="flex items-center gap-3 min-w-0">
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
              <div class="flex items-center gap-2 ml-4 shrink-0">
                <button v-if="expandedAdd !== u.id" @click="openAddForm(u)" type="button"
                  class="text-xs font-bold px-3 py-1.5 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-500 hover:text-white transition">
                  + Add to Attractions
                </button>
                <button v-else @click="closeAddForm" type="button"
                  class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-100 transition">
                  Cancel
                </button>
                <button @click="markReviewed(u.id)" :disabled="reviewForm.processing" type="button"
                  class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition disabled:opacity-50">
                  ✓ Dismiss
                </button>
              </div>
            </div>

            <div v-if="expandedAdd === u.id" class="px-4 py-4 bg-white border-t border-amber-100">
              <p class="text-xs font-semibold text-gray-600 mb-3">
                Add <span class="text-blue-600">"{{ u.name }}"</span> to the official Attractions list:
              </p>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Attraction Name</label>
                  <input v-model="addAttrForm.name" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none" />
                  <p v-if="addAttrForm.errors.name" class="text-red-500 text-xs mt-1">{{ addAttrForm.errors.name }}</p>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Type</label>
                  <select v-model="addAttrForm.type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option v-for="t in attractionTypes" :key="t" :value="t">{{ t }}</option>
                  </select>
                </div>
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
                <button @click="submitAddFromUnrecognized(u.id)"
                  :disabled="addAttrForm.processing || !addAttrForm.name.trim()" type="button"
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
  unreviewed:           { type: Array, default: () => [] },
});

const page        = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const userRole    = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase());
const can         = (p) => userRole.value === 'admin' || permissions.value.includes(p);

const attractionTypes = ['Resort', 'Beach', 'Falls', 'Landmark', 'Hiking Trail', 'Park', 'Cave', 'Viewpoint', 'General'];

let tempCounter = 0;
const newTempKey = () => `new-${++tempCounter}`;

const toFeeRow   = (r) => ({ ...r, _key: `db-${r.id}` });
const toSitioRow = (r) => ({ ...r, _key: `db-${r.id}`, is_active: Boolean(r.is_active) });
const toAttrRow  = (r) => ({
    ...r,
    _key:      `db-${r.id}`,
    sitio_id:  r.sitio_id != null ? Number(r.sitio_id) : null,
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

// Watch always syncs from props — no guard needed.
// Cancel uses a saved snapshot instead of re-reading props.
// This ensures the table always shows the latest DB data after save.
watch(() => props.feeCategories, (v) => {
    feeRows.value = v.map(toFeeRow);
}, { deep: true });

const feeAllSelected = computed(() =>
    feeRows.value.length > 0 && feeSelected.value.length === feeRows.value.length);

// Snapshot taken at Edit click — used to restore on Cancel
let feeSnapshot = [];
function startFeeEdit() {
    feeSnapshot    = feeRows.value.map(r => ({ ...r }));
    feeEditing.value = true;
}
function cancelFeeEdit() {
    feeEditing.value = false; feeAddingNew.value = false; feeSelected.value = [];
    feeRows.value    = feeSnapshot.map(r => ({ ...r })); // restore snapshot, not props
}
function feeToggleAll(e) { feeSelected.value = e.target.checked ? feeRows.value.map(r => r._key) : []; }
function feeStartAdd()   { feeAddingNew.value = true; feeNewRow.value = { category: '', age_range: '', fee: '' }; }
function feeConfirmAdd() {
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
        // Inertia updates props reactively after success — the watch picks it up
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
    sitioRows.value = v.map(toSitioRow);
}, { deep: true });

const sitioAllSelected = computed(() =>
    sitioRows.value.length > 0 && sitioSelected.value.length === sitioRows.value.length);

let sitioSnapshot = [];
function startSitioEdit() {
    sitioSnapshot      = sitioRows.value.map(r => ({ ...r }));
    sitioEditing.value = true;
}
function cancelSitioEdit() {
    sitioEditing.value = false; sitioAddingNew.value = false; sitioSelected.value = [];
    sitioRows.value    = sitioSnapshot.map(r => ({ ...r }));
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
    attrRows.value = v.map(toAttrRow);
}, { deep: true });

const attrAllSelected = computed(() =>
    attrRows.value.length > 0 && attrSelected.value.length === attrRows.value.length);

let attrSnapshot = [];
function startAttrEdit() {
    attrSnapshot      = attrRows.value.map(r => ({ ...r }));
    attrEditing.value = true;
}
function cancelAttrEdit() {
    attrEditing.value = false; attrAddingNew.value = false; attrSelected.value = [];
    attrRows.value    = attrSnapshot.map(r => ({ ...r }));
}
function attrToggleAll(e) { attrSelected.value = e.target.checked ? attrRows.value.map(r => r._key) : []; }
function attrStartAdd() {
    attrAddingNew.value = true;
    attrNewRow.value = { name: '', type: 'General', description: '', sitio_id: null, is_active: true };
}
function attrConfirmAdd() {
    if (!attrNewRow.value.name.trim()) return;
    const sitio = sitioRows.value.find(s => Number(s.id) === attrNewRow.value.sitio_id);
    attrRows.value.push({
        ...attrNewRow.value,
        id:         null,
        _key:       newTempKey(),
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
// CARD 4 — New Destination Discoveries
// ─────────────────────────────────────────────────────────────────────────────
const expandedAdd = ref(null);
const addAttrForm = useForm({ name: '', type: 'General', sitio_id: null });

function openAddForm(u) {
    expandedAdd.value = u.id;
    addAttrForm.name     = u.name;
    addAttrForm.type     = 'General';
    addAttrForm.sitio_id = null;
    addAttrForm.clearErrors();
}
function closeAddForm() { expandedAdd.value = null; addAttrForm.reset(); }

function submitAddFromUnrecognized(unreviewedId) {
    addAttrForm.post(route('fee-categories.add-from-unrecognized', unreviewedId), {
        preserveScroll: true,
        onSuccess: () => { expandedAdd.value = null; addAttrForm.reset(); },
    });
}

const reviewForm = useForm({});
function markReviewed(id) {
    reviewForm.patch(route('fee-categories.review-unrecognized', id), { preserveScroll: true });
}

// ─────────────────────────────────────────────────────────────────────────────
// Nav helper
// ─────────────────────────────────────────────────────────────────────────────
const navClass = (routeName) => [
    'pb-2 text-sm font-semibold transition border-b-2',
    route().current(routeName)
        ? 'text-gray-900 border-gray-900'
        : 'text-gray-400 border-transparent hover:text-gray-600',
];
</script>