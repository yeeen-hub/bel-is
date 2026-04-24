<template>
    <LandingLayout>
        <!-- Top Bar with search -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">
                <div class="relative flex-1">
                    <input v-model="search" type="text" placeholder="Search by name or place of origin..." :class="[
                        'w-full p-2 pl-8 rounded-lg border text-sm transition-colors duration-200',
                        search ? 'border-gray-800 bg-white ring-1 ring-gray-800' : 'border-gray-300 bg-white focus:border-gray-400'
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
                <FontAwesomeIcon icon="bell" />
                <FontAwesomeIcon icon="user" />
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-4 sm:mt-5 mb-4 sm:mb-5">
            Reports / Analytics
        </p>

        <!-- Dropdown -->
        <div class="relative w-full sm:w-1/2 lg:w-1/5 mb-3 sm:mb-0" ref="dropdownRef">
            <button type="button" @click="openDdreports = !openDdreports"
                class="w-full border py-2 px-3 rounded text-left text-sm">
                {{ ddreports || 'Overview' }}
            </button>

            <div v-if="openDdreports" class="absolute left-0 w-full mt-1 bg-white border rounded shadow z-10">
                <Link v-for="option in purposeOptions" :key="option.label" :href="option.link"
                    class="block px-3 py-2 hover:bg-gray-100 text-sm">
                    {{ option.label }}
                </Link>
            </div>
        </div>

        <!-- Controls -->
        <div class="mt-4 sm:mt-5">

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">

                <!-- LEFT -->
                <div class="flex flex-wrap items-center gap-2">

                    <button ref="filterBtnRef" @click="toggleFilter"
                        class="flex items-center gap-1.5 text-sm border px-3 py-2 rounded transition h-10" :class="activeFilterCount > 0
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
                            ✕
                        </button>
                    </span>

                </div>

                <!-- RIGHT -->
                <div class="flex flex-wrap gap-2">
                    <button class="h-10 border border-gray-900 font-bold px-3 text-sm rounded-lg w-full sm:w-auto">
                        Export PDF
                    </button>

                    <button class="h-10 border border-gray-900 font-bold px-3 text-sm rounded-lg w-full sm:w-auto">
                        Export EXCEL
                    </button>
                </div>

            </div>

            <!-- FILTER PANEL -->
            <div v-if="showFilter" ref="filterRef" class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 mt-3
           grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">

                <!-- Purpose -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Purpose of Visit</label>
                    <select v-model="purpose" class="w-full border rounded py-2 px-2 text-sm">
                        <option value="">All Purposes</option>
                        <option value="Tourism">Tourism</option>
                        <option value="Research">Research</option>
                        <option value="Event">Event</option>
                        <option value="Official Visit">Official Visit</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Area -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Area (Sitio)</label>
                    <select v-model="area" class="w-full border rounded py-2 px-2 text-sm">
                        <option value="">All Areas</option>
                        <option v-for="s in sitios" :key="s.id" :value="s.name">{{ s.name }}</option>
                    </select>
                </div>

                <!-- Attraction -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Attraction</label>
                    <select v-model="attractionId" class="w-full border rounded py-2 px-2 text-sm">
                        <option value="">All Attractions</option>
                        <option v-for="a in attractions" :key="a.id" :value="a.id">{{ a.name }}</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Date From</label>
                    <input v-model="dateFrom" type="date" class="w-full border rounded py-2 px-2 text-sm" />
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Date To</label>
                    <input v-model="dateTo" type="date" class="w-full border rounded py-2 px-2 text-sm" />
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-2 sm:justify-end lg:col-span-3">

                    <button @click="clearFilters"
                        class="text-sm text-gray-500 border border-gray-300 px-3 py-1.5 rounded hover:bg-gray-100 w-full sm:w-auto">
                        Clear All
                    </button>

                    <button @click="applyFilters"
                        class="bg-gray-900 text-white text-sm font-bold px-4 py-1.5 rounded hover:bg-gray-700 w-full sm:w-auto">
                        Apply Filters
                    </button>

                </div>
            </div>
        </div>

        <!-- TABLE (mobile safe) -->
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-[700px] w-full text-center text-sm bg-white rounded-lg shadow-md">

                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2">Name</th>
                        <th class="p-2">Place of Origin</th>
                        <th class="p-2">Purpose of Visit</th>
                        <th class="p-2">Duration of Stay</th>
                        <th class="p-2">Destination</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(row, i) in rows" :key="i" class="hover:bg-gray-100">
                        <td class="p-2 border-b">{{ row.full_name }}</td>
                        <td class="p-2 border-b">{{ row.place_of_origin }}</td>
                        <td class="p-2 border-b">{{ row.purpose }}</td>
                        <td class="p-2 border-b">{{ row.duration_of_stay }}</td>
                        <td class="p-2 border-b text-left text-xs text-gray-600">
                            {{ row.destinations }}
                        </td>
                    </tr>

                    <tr v-if="rows.length === 0">
                        <td colspan="5" class="p-8 text-center text-gray-400 text-sm">
                            No data found.
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>
    </LandingLayout>
</template>

<script>
import LandingLayout from '@/Layouts/SidebarLayout.vue';
export default { components: { LandingLayout } }
</script>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    rows: { type: Array, default: () => [] },
    sitios: { type: Array, default: () => [] },
    attractions: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

const ddreports = ref('')
const openDdreports = ref(false)
const dropdownRef = ref(null)
const purposeOptions = [{ label: 'Demographics', link: route('reports.demographics') }]

// Filter state
const search = ref(props.filters.search ?? '')
const purpose = ref(props.filters.purpose ?? '')
const area = ref(props.filters.area ?? '')
const attractionId = ref(props.filters.attraction_id ?? '')
const dateFrom = ref(props.filters.date_from ?? '')
const dateTo = ref(props.filters.date_to ?? '')
const showFilter = ref(false)
const filterRef = ref(null)
const filterBtnRef = ref(null)

const activeFilterCount = computed(() => {
    let n = 0
    if (purpose.value) n++
    if (area.value) n++
    if (attractionId.value) n++
    if (dateFrom.value) n++
    if (dateTo.value) n++
    return n
})

const activeChips = computed(() => {
    const chips = []
    if (search.value) chips.push({ key: 'search', label: `Search: "${search.value}"` })
    if (purpose.value) chips.push({ key: 'purpose', label: `Purpose: ${purpose.value}` })
    if (area.value) chips.push({ key: 'area', label: `Area: ${area.value}` })
    if (attractionId.value) {
        const a = props.attractions.find(x => x.id == attractionId.value)
        chips.push({ key: 'attraction_id', label: `Attraction: ${a?.name ?? attractionId.value}` })
    }
    if (dateFrom.value) chips.push({ key: 'date_from', label: `From: ${dateFrom.value}` })
    if (dateTo.value) chips.push({ key: 'date_to', label: `To: ${dateTo.value}` })
    return chips
})

const removeChip = (key) => {
    if (key === 'search') search.value = ''
    if (key === 'purpose') purpose.value = ''
    if (key === 'area') area.value = ''
    if (key === 'attraction_id') attractionId.value = ''
    if (key === 'date_from') dateFrom.value = ''
    if (key === 'date_to') dateTo.value = ''
    applyFilters()
}

const applyFilters = () => {
    router.get(route('reports.analytics'), {
        search: search.value || undefined,
        purpose: purpose.value || undefined,
        area: area.value || undefined,
        attraction_id: attractionId.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true })
}

const clearFilters = () => {
    search.value = purpose.value = area.value = attractionId.value = dateFrom.value = dateTo.value = ''
    applyFilters()
}

let searchTimer = null
watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
})

const toggleFilter = () => { showFilter.value = !showFilter.value }

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) openDdreports.value = false
    if (
        showFilter.value &&
        filterRef.value && !filterRef.value.contains(e.target) &&
        filterBtnRef.value && !filterBtnRef.value.contains(e.target)
    ) showFilter.value = false
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>