<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const permissions = computed(() => page.props.auth?.permissions ?? []);
const userRole = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase());

const can = (permission) => {
    if (userRole.value === 'admin') return true;
	return permissions.value.includes(permission);
};

const props = defineProps({
    visitors: Object,
    filters:  Object,
    // ✅ pending_fees passed from VisitorController (add to controller below)
    pendingFees: {
        type: Number,
        default: 0,
    },
})

// ── Filter State ──────────────────────────────────────────────────────────────
const search     = ref(props.filters?.search     ?? '')
const purpose    = ref(props.filters?.purpose    ?? '')
const feeStatus  = ref(props.filters?.fee_status ?? '')
const dateFrom   = ref(props.filters?.date_from  ?? '')
const dateTo     = ref(props.filters?.date_to    ?? '')
const showFilter = ref(false)

// ── Active Filter Count (for badge) ──────────────────────────────────────────
const activeFilterCount = computed(() => {
    let count = 0
    if (purpose.value)  count++
    if (feeStatus.value) count++
    if (dateFrom.value)  count++
    if (dateTo.value)    count++
    return count
})

// ── Active Filter Chips ───────────────────────────────────────────────────────
const activeChips = computed(() => {
    const chips = []
    if (search.value)    chips.push({ key: 'search',     label: `Search: "${search.value}"` })
    if (purpose.value)   chips.push({ key: 'purpose',    label: `Purpose: ${purpose.value}` })
    if (feeStatus.value)  chips.push({ key: 'fee_status', label: `Status: ${feeStatus.value}` })
    if (dateFrom.value)   chips.push({ key: 'date_from',  label: `From: ${dateFrom.value}` })
    if (dateTo.value)     chips.push({ key: 'date_to',    label: `To: ${dateTo.value}` })
    return chips
})

const hasActiveFilters = computed(() => activeChips.value.length > 0)

// ── Remove a single chip ──────────────────────────────────────────────────────
const removeChip = (key) => {
    if (key === 'search')     search.value    = ''
    if (key === 'purpose')    purpose.value   = ''
    if (key === 'fee_status') feeStatus.value = ''
    if (key === 'date_from')  dateFrom.value  = ''
    if (key === 'date_to')    dateTo.value    = ''
    applyFilters()
}

// ── Apply Filters via Inertia ─────────────────────────────────────────────────
const applyFilters = () => {
    router.get(route('visitor-records'), {
        search:     search.value     || undefined,
        purpose:    purpose.value    || undefined,
        fee_status: feeStatus.value  || undefined,
        date_from:  dateFrom.value   || undefined,
        date_to:    dateTo.value     || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

// ── Clear All Filters ─────────────────────────────────────────────────────────
const clearFilters = () => {
    search.value    = ''
    purpose.value   = ''
    feeStatus.value = ''
    dateFrom.value  = ''
    dateTo.value    = ''
    applyFilters()
}

// ── Real-time Search (debounced 400ms) ────────────────────────────────────────
let searchTimeout = null
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => applyFilters(), 400)
})

// ── Notification Bell ─────────────────────────────────────────────────────────
const showNotifications = ref(false)
const bellRef           = ref(null)

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value
    // Close filter panel when bell opens
    if (showNotifications.value) showFilter.value = false
}

// ── Filter Panel ref (for click-outside) ─────────────────────────────────────
const filterRef       = ref(null)
const filterBtnRef    = ref(null)

const toggleFilter = () => {
    showFilter.value = !showFilter.value
    // Close bell when filter opens
    if (showFilter.value) showNotifications.value = false
}

// ── Click Outside Handler ─────────────────────────────────────────────────────
const handleClickOutside = (e) => {
    // Close filter panel
    if (
        showFilter.value &&
        filterRef.value &&
        !filterRef.value.contains(e.target) &&
        filterBtnRef.value &&
        !filterBtnRef.value.contains(e.target)
    ) {
        showFilter.value = false
    }

    // Close bell dropdown
    if (bellRef.value && !bellRef.value.contains(e.target)) {
        showNotifications.value = false
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

// ── Visitor Detail Modal ──────────────────────────────────────────────────────
const showModal       = ref(false)
const selectedVisitor = ref(null)

const openModal = (visitor) => {
    selectedVisitor.value = visitor
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedVisitor.value = null
}

</script>

<template>
    <LandingLayout>

        <!-- Top Bar -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">

                <!-- Real-time search with active indicator -->
                <div class="relative flex-1">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by name, origin, or registration ID..."
                        :class="[
                            'w-full p-2 pl-8 rounded-lg border text-sm transition-colors duration-200',
                            search
                                ? 'border-gray-800 bg-white ring-1 ring-gray-800'
                                : 'border-gray-300 bg-white focus:border-gray-400'
                        ]"
                    />
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4"
                        :class="search ? 'text-gray-800' : 'text-gray-400'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span v-if="search"
                        class="absolute right-2.5 top-2 text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                        searching...
                    </span>
                </div>

                <!-- ✅ Notification Bell — same as dashboard -->
                <div class="relative" ref="bellRef">
                    <button @click="toggleNotifications" class="relative focus:outline-none">
                        <FontAwesomeIcon icon="bell" class="text-gray-700 text-lg" />
                        <span v-if="pendingFees > 0"
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center">
                            {{ pendingFees > 9 ? '9+' : pendingFees }}
                        </span>
                    </button>

                    <!-- Notification Dropdown -->
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
                                    <button
                                        @click="feeStatus = 'Pending'; showNotifications = false; applyFilters()"
                                        class="text-xs text-yellow-600 font-semibold mt-1 inline-block hover:underline">
                                        Show Pending Records →
                                    </button>
                                </div>
                            </div>
                            <div v-if="pendingFees === 0"
                                class="px-4 py-8 text-center text-gray-400 text-sm">
                                <FontAwesomeIcon icon="bell" class="text-gray-300 text-2xl mb-2 block mx-auto" />
                                <p>No new notifications</p>
                            </div>
                        </div>
                    </div>
                </div>

                <FontAwesomeIcon icon="user" class="text-gray-700" />
            </div>
        </div>

        <div class="bg-gray-100 p-4 mt-4 rounded-lg flex flex-col">

            <!-- Header Row -->
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Tourist Records</h1>
                    <p class="text-xs mt-0.5"
                        :class="hasActiveFilters ? 'text-gray-800 font-semibold' : 'text-gray-500'">
                        <span v-if="hasActiveFilters">
                            Showing {{ visitors.total }} filtered result(s)
                            
                        </span>
                        <span v-else>
                            {{ visitors.total }} total record(s)
                        </span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <!-- ✅ Filter button with ref for click-outside -->
                    <button
                        ref="filterBtnRef"
                        @click="toggleFilter"
                        class="relative flex items-center gap-1.5 text-sm border px-3 py-2 rounded transition"
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
                </div>
            </div>

            <!-- Active Filter Chips -->
            <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mb-3">
                <span v-for="chip in activeChips" :key="chip.key"
                    class="flex items-center gap-1 bg-gray-800 text-white text-xs px-2 py-1 rounded-full">
                    {{ chip.label }}
                    <button @click="removeChip(chip.key)" class="ml-1 hover:text-gray-300">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
                
            </div>

            <!-- ✅ Filter Panel with ref for click-outside -->
            <div
                v-if="showFilter"
                ref="filterRef"
                class="bg-white border border-gray-200 rounded-lg p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Purpose of Visit</label>
                    <select v-model="purpose"
                        class="w-full border rounded py-2 px-2 text-sm text-gray-700 focus:ring-0 focus:border-gray-400">
                        <option value="">All Purposes</option>
                        <option value="Tourism">Tourism</option>
                        <option value="Research">Research</option>
                        <option value="Event">Event</option>
                        <option value="Official Visit">Official Visit</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Fee Status</label>
                    <select v-model="feeStatus"
                        class="w-full border rounded py-2 px-2 text-sm text-gray-700 focus:ring-0 focus:border-gray-400">
                        <option value="">All Status</option>
                        <option value="Collected">Collected</option>
                        <option value="Pending">Pending</option>
                        <option value="Waived">Waived</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Date From</label>
                    <input v-model="dateFrom" type="date"
                        class="w-full border rounded py-2 px-2 text-sm text-gray-700 focus:ring-0 focus:border-gray-400" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Date To</label>
                    <input v-model="dateTo" type="date"
                        class="w-full border rounded py-2 px-2 text-sm text-gray-700 focus:ring-0 focus:border-gray-400" />
                </div>

                <div class="md:col-span-4 flex gap-2 justify-end">
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

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="text-gray-500 text-xs uppercase border-b bg-gray-50">
                            <th class="p-3">Reg. ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Place of Origin</th>
                            <th class="p-3">Purpose</th>
                            <th class="p-3">Duration</th>
                            <th class="p-3">Fee Status</th>
                            <th class="p-3">Date of Arrival</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="visitors.data.length === 0">
                            <td colspan="7" class="p-8 text-center">
                                <div v-if="hasActiveFilters">
                                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">No results found</p>
                                    <p class="text-gray-400 text-xs mt-1">No records match your current filters.</p>
                                    <button @click="clearFilters"
                                        class="mt-3 text-xs text-gray-800 underline hover:text-gray-600">
                                        Clear all filters
                                    </button>
                                </div>
                                <div v-else>
                                    <p class="text-gray-400">No tourist records yet.</p>
                                </div>
                            </td>
                        </tr>

                        <tr
                            v-for="visitor in visitors.data"
                            :key="visitor.id"
                            @click="openModal(visitor)"
                            class="cursor-pointer hover:bg-blue-50 border-b last:border-0 transition-colors duration-100">
                            <td class="p-3 text-gray-400 text-xs font-mono">{{ visitor.registration_id }}</td>
                            <td class="p-3 font-medium text-gray-800">{{ visitor.name }}</td>
                            <td class="p-3 text-gray-600">{{ visitor.place_of_origin }}</td>
                            <td class="p-3 text-gray-600">{{ visitor.purpose }}</td>
                            <td class="p-3 text-gray-600">{{ visitor.duration }}</td>
                            <td class="p-3">
                                <span :class="{
                                    'bg-green-100 text-green-700':   visitor.fee_status === 'Collected',
                                    'bg-yellow-100 text-yellow-700': visitor.fee_status === 'Pending',
                                    'bg-gray-100 text-gray-500':     visitor.fee_status === 'Waived',
                                }" class="px-2 py-1 rounded-full text-xs font-bold">
                                    {{ visitor.fee_status }}
                                </span>
                            </td>
                            <td class="p-3 text-gray-500 text-xs">{{ visitor.arrival_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-4 text-sm text-gray-600">
                <p class="text-xs text-gray-500">
                    Showing {{ visitors.from ?? 0 }}–{{ visitors.to ?? 0 }} of {{ visitors.total }} record(s)
                    <span v-if="hasActiveFilters" class="text-yellow-600 font-semibold">(filtered)</span>
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in visitors.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        :class="{
                            'bg-gray-900 text-white border-gray-900': link.active,
                            'text-gray-300 pointer-events-none cursor-not-allowed': !link.url,
                            'hover:bg-gray-200 text-gray-700': link.url && !link.active,
                        }"
                        class="px-3 py-1 rounded text-xs border border-gray-200"
                        preserve-scroll
                    />
                </div>
            </div>

        </div>

        <!-- Visitor Detail Modal -->
        <div v-if="showModal && selectedVisitor"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            @click.self="closeModal">

            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg mx-4 max-h-screen overflow-y-auto">

                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs text-gray-500">Tourist Profile</p>
                    <button @click="closeModal">
                        <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none">
                            <path d="M7 17L16.8995 7.10051" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7 7.00001L16.8995 16.8995" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ selectedVisitor.name }}</h2>
                        <p class="text-xs font-mono text-gray-400 mt-0.5">{{ selectedVisitor.registration_id }}</p>
                    </div>
                    <span :class="{
                        'bg-green-100 text-green-700':   selectedVisitor.fee_status === 'Collected',
                        'bg-yellow-100 text-yellow-700': selectedVisitor.fee_status === 'Pending',
                        'bg-gray-100 text-gray-500':     selectedVisitor.fee_status === 'Waived',
                    }" class="px-3 py-1 rounded-full text-xs font-bold mt-1">
                        {{ selectedVisitor.fee_status }}
                    </span>
                </div>

                <hr class="my-3 border-gray-200">

                <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Place of Origin</h4>
                <div class="bg-gray-50 rounded p-3 text-sm text-gray-700 mb-4">
                    {{ selectedVisitor.place_of_origin }}
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-1">Purpose of Visit</h4>
                        <p class="text-sm text-gray-800 font-medium">{{ selectedVisitor.purpose }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-1">Duration of Stay</h4>
                        <p class="text-sm text-gray-800 font-medium">{{ selectedVisitor.duration }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-1">Date of Arrival</h4>
                        <p class="text-sm text-gray-800 font-medium">{{ selectedVisitor.arrival_at }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-1">Contact Number</h4>
                        <p class="text-sm text-gray-800 font-medium">{{ selectedVisitor.contact_number }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-1">Registered By</h4>
                        <p class="text-sm text-gray-800 font-medium">{{ selectedVisitor.registered_by }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-1">Environmental Fee</h4>
                        <p class="text-sm font-bold" :class="{
                            'text-green-700':  selectedVisitor.fee_status === 'Collected',
                            'text-yellow-700': selectedVisitor.fee_status === 'Pending',
                            'text-gray-500':   selectedVisitor.fee_status === 'Waived',
                        }">{{ selectedVisitor.fee_status }}</p>
                    </div>
                </div>

                <hr class="my-3 border-gray-200">

                <div class="flex gap-3 justify-end mt-2">
                <!-- Close Button -->
                <button @click="closeModal"
                    class="text-sm text-gray-500 border border-gray-300 px-4 py-2 rounded hover:bg-gray-100">
                    Close
                </button>

                <!-- Collect Fee Link (Requires EDIT permission) -->
                <Link v-if="selectedVisitor.fee_status === 'Pending'"
                    :href="route('adminpay', selectedVisitor.id)"
                    :class="[
                        'text-sm font-bold px-4 py-2 rounded transition-all',
                        !can('edit_visitor_records') 
                            ? 'bg-yellow-200 text-yellow-700 cursor-not-allowed pointer-events-none opacity-60' 
                            : 'bg-yellow-500 text-white hover:bg-yellow-600'
                    ]"
                    :title="!can('edit_visitor_records') ? 'Permission Denied: Cannot collect fees' : ''">
                    Collect Fee
                </Link>

                <!-- View Receipt Link (Requires VIEW permission) -->
                <Link v-if="selectedVisitor.fee_status === 'Collected' || selectedVisitor.fee_status === 'Waived'"
                    :href="route('adminreceipt', selectedVisitor.id)"
                    :class="[
                        'text-sm font-bold px-4 py-2 rounded transition-all',
                        !can('view_visitor_records') 
                            ? 'bg-gray-300 text-gray-500 cursor-not-allowed pointer-events-none' 
                            : 'bg-gray-900 text-white hover:bg-gray-700'
                    ]"
                    :title="!can('view_visitor_records') ? 'Permission Denied: Cannot view receipts' : ''">
                    View Receipt
                </Link>
            </div>


                

            </div>
        </div>

    </LandingLayout>
</template>