<template>
    <LandingLayout>
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">

                <div class="relative flex-1">
                    <input v-model="search" type="text" placeholder="Search..." :class="[
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
                    <span v-if="search"
                        class="absolute right-2.5 top-2 text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
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
                                    <button @click="feeStatus = 'Pending'; showNotifications = false; applyFilters()"
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

                <div class="relative">
                    <button @click="showUser = !showUser">
                        <FontAwesomeIcon icon="user" class="text-gray-700 text-lg" />
                    </button>
                    <!-- dropdown -->
                    <div v-if="showUser"
                        class="absolute right-0 mt-3 w-52 bg-white/90 backdrop-blur-md border border-gray-200 rounded-xl shadow-xl p-4 z-50 text-center">

                        <!-- User Name -->
                        <p class="text-sm font-semibold text-gray-800 truncate">
                            {{ authUser?.name }}
                        </p>

                    </div>
                </div>
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-5 mb-5">Reports / Fee Revenue</p>

        <h1 class="font-heading text-gray-800 font-semibold text-2xl">Dashboard</h1>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 p-4 gap-4 mt-4">
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-gray-800 font-medium text-sm">Total Revenue</h2>
                <p class="text-2xl font-bold text-gray-800">{{ totalRevenue }} php</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-gray-800 font-medium text-sm">Average Daily Revenue</h2>
                <p class="text-2xl font-bold text-gray-800">{{ avgDaily }} php</p>
            </div>
        </div>

        <div class="mt-5">
            <div class="flex items-center justify-between w-full">

                <!-- LEFT: Filters + chips -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button ref="filterBtnRef" @click="toggleFilter"
                        class="relative flex items-center gap-1.5 text-sm border px-3 py-2 rounded transition h-10"
                        :class="activeFilterCount > 0
                            ? 'border-gray-800 bg-gray-900 text-white'
                            : 'border-gray-300 text-gray-700 hover:bg-gray-200'">
                        <FontAwesomeIcon icon="filter" class="text-xs" />
                        Filters
                        <span v-if="activeFilterCount > 0"
                            class="bg-white text-gray-900 text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center">
                            {{ activeFilterCount }}
                        </span>
                    </button>
                    <span v-for="chip in activeChips" :key="chip.key"
                        class="flex items-center gap-1 bg-gray-800 text-white text-xs px-2 py-1 rounded-full h-7">
                        {{ chip.label }}
                        <button @click="removeChip(chip.key)" class="ml-1 hover:text-gray-300">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </span>
                </div>

                <!-- RIGHT: Export buttons — open modal -->
                <div class="flex items-center gap-2">
                    <button @click="applyFilters"
                        class="h-10 bg-gray-900 text-white font-bold px-4 text-sm rounded">
                        Enter
                    </button>
                    <button @click="showExportModal = true"
                        class="h-10 border border-gray-900 font-bold px-3 text-sm rounded-lg hover:bg-gray-900 hover:text-white transition">
                        Export Excel
                    </button>
                    <button @click="showExportModal = true"
                        class="h-10 bg-gray-900 text-white font-bold px-3 text-sm rounded-lg hover:bg-black transition">
                        Export PDF
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div v-if="showFilter" ref="filterRef"
                class="bg-white border border-gray-200 rounded-lg p-4 mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Visit Category</label>
                    <select v-model="category"
                        class="w-full border rounded py-2 px-2 text-sm text-gray-700 focus:ring-0 focus:border-gray-400">
                        <option value="">All Categories</option>
                        <option value="Adult">Adult</option>
                        <option value="Child">Child</option>
                        <option value="Senior Citizen">Senior Citizen</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Fee Status</label>
                    <select v-model="feeType"
                        class="w-full border rounded py-2 px-2 text-sm text-gray-700 focus:ring-0 focus:border-gray-400">
                        <option value="">All Statuses</option>
                        <option value="Standard">Collected (Standard)</option>
                        <option value="Waived">Waived</option>
                        <option value="Pending">Pending</option>
                        <option value="No Show">No Show</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Area (Sitio)</label>
                    <select v-model="area"
                        class="w-full border rounded py-2 px-2 text-sm text-gray-700 focus:ring-0 focus:border-gray-400">
                        <option value="">All Areas</option>
                        <option v-for="s in sitios" :key="s.id" :value="s.name">{{ s.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Date From</label>
                    <input v-model="dateFrom" type="date"
                        class="w-full border rounded py-2 px-2 text-sm text-gray-700 focus:ring-0 focus:border-gray-400"/>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Date To</label>
                    <input v-model="dateTo" type="date"
                        class="w-full border rounded py-2 px-2 text-sm text-gray-700 focus:ring-0 focus:border-gray-400"/>
                </div>

                <div class="md:col-span-3 flex gap-2 justify-end items-end">
                    <button @click="clearFilters"
                        class="text-sm text-gray-500 border border-gray-300 px-3 py-1.5 rounded hover:bg-gray-100">
                        Clear All
                    </button>
                    <button @click="applyFilters"
                        class="bg-gray-900 text-white text-sm font-bold px-4 py-1.5 rounded hover:bg-gray-700">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full text-left mt-5 border-collapse text-center bg-white rounded-lg shadow-md">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-2 text-black">Visit Category</th>
                    <th class="p-2 text-black">Name</th>
                    <th class="p-2 text-black">Revenue</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, i) in rows" :key="i" class="cursor-pointer hover:bg-gray-100">
                    <td class="p-2 border-b">{{ row.visit_category }}</td>
                    <td class="p-2 border-b">{{ row.full_name }}</td>
                    <td class="p-2 border-b">{{ row.revenue }}</td>
                </tr>
                <tr v-if="rows.length === 0">
                    <td colspan="3" class="p-8 text-center text-gray-400 text-sm">No data found.</td>
                </tr>
            </tbody>
        </table>

        <!-- Export Modal -->
        <ExportModal
            :show="showExportModal"
            report-type="fee-revenue"
            default-title="Fee Revenue Report"
            :column-defs="columnDefs"
            :filtered-rows="rows"
            :all-rows="allRows"
            :sitios="sitios"
            @close="showExportModal = false"
        />
    </LandingLayout>
</template>

<script>
import LandingLayout from '@/Layouts/SidebarLayout.vue';
export default { components: { LandingLayout } }
</script>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import ExportModal from '@/Components/ExportModal.vue'

const page = usePage()
const authUser = computed(() => page.props.auth?.user)
const showUser = ref(false)

const props = defineProps({
    rows:         { type: Array,  default: () => [] },
    allRows:      { type: Array,  default: () => [] },
    totalRevenue: { type: String, default: '0.00' },
    avgDaily:     { type: String, default: '0.00' },
    sitios:       { type: Array,  default: () => [] },
    filters:      { type: Object, default: () => ({}) },
})

// ── Column definitions for this report ───────────────────────────────────────
const columnDefs = [
    { key: 'visit_category', label: 'Visit Category' },
    { key: 'full_name',      label: 'Name'           },
    { key: 'revenue',        label: 'Revenue (₱)'    },
]

const showExportModal = ref(false)

const search   = ref(props.filters.search    ?? '')
const category = ref(props.filters.category  ?? '')
const feeType  = ref(props.filters.fee_type  ?? '')
const area     = ref(props.filters.area      ?? '')
const dateFrom = ref(props.filters.date_from ?? '')
const dateTo   = ref(props.filters.date_to   ?? '')
const showFilter   = ref(false)
const filterRef    = ref(null)
const filterBtnRef = ref(null)

const activeFilterCount = computed(() => {
    let n = 0
    if (category.value) n++
    if (feeType.value)  n++
    if (area.value)     n++
    if (dateFrom.value) n++
    if (dateTo.value)   n++
    return n
})

const activeChips = computed(() => {
    const chips = []
    if (search.value)   chips.push({ key: 'search',    label: `Search: "${search.value}"` })
    if (category.value) chips.push({ key: 'category',  label: `Category: ${category.value}` })
    if (feeType.value)  chips.push({ key: 'fee_type',  label: `Status: ${feeType.value}` })
    if (area.value)     chips.push({ key: 'area',      label: `Area: ${area.value}` })
    if (dateFrom.value) chips.push({ key: 'date_from', label: `From: ${dateFrom.value}` })
    if (dateTo.value)   chips.push({ key: 'date_to',   label: `To: ${dateTo.value}` })
    return chips
})

const removeChip = (key) => {
    if (key === 'search')    search.value   = ''
    if (key === 'category')  category.value = ''
    if (key === 'fee_type')  feeType.value  = ''
    if (key === 'area')      area.value     = ''
    if (key === 'date_from') dateFrom.value = ''
    if (key === 'date_to')   dateTo.value   = ''
    applyFilters()
}

const applyFilters = () => {
    router.get(route('reports.fee-revenue'), {
        search:    search.value    || undefined,
        category:  category.value  || undefined,
        fee_type:  feeType.value   || undefined,
        area:      area.value      || undefined,
        date_from: dateFrom.value  || undefined,
        date_to:   dateTo.value    || undefined,
    }, { preserveState: true, replace: true })
}

const clearFilters = () => {
    search.value = category.value = feeType.value = area.value = dateFrom.value = dateTo.value = ''
    applyFilters()
}

let searchTimer = null
watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
})

const toggleFilter = () => { showFilter.value = !showFilter.value }

const handleClickOutside = (e) => {
    if (
        showFilter.value &&
        filterRef.value && !filterRef.value.contains(e.target) &&
        filterBtnRef.value && !filterBtnRef.value.contains(e.target)
    ) showFilter.value = false
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>