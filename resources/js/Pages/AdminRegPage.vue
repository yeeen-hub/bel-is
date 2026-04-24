<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'
import DestinationChecklist from '@/Components/DestinationChecklist.vue'
import axios from 'axios'

const page = usePage()
const permissions = computed(() => page.props.auth?.permissions ?? [])
const userRole    = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase())
const can = (permission) => {
    if (userRole.value === 'admin') return true
    return permissions.value.includes(permission)
}

const props = defineProps({
    feeCategories:        { type: Array, default: () => [] },
    barangayAttractions:  { type: Array, default: () => [] },
})

// ── Mode toggle ───────────────────────────────────────────────────────────────
const mode = ref('single')

// ── Shared options ────────────────────────────────────────────────────────────
const purposeOptions  = ['Tourism', 'Research', 'Event', 'Official Visit', 'Other']
const durationOptions = ['1 day', '2 days', '3 days', '4-7 days', 'More than 1 week']

// ── Profile search helper ─────────────────────────────────────────────────────
const makeSearchState = () => ({
    query: '', results: [], loading: false, selected: null, timer: null,
})

const runProfileSearch = async (state) => {
    clearTimeout(state.timer)
    if (state.query.length < 2) { state.results = []; return }
    state.timer = setTimeout(async () => {
        state.loading = true
        try {
            const res = await axios.get(route('visitors.search-profile'), { params: { query: state.query } })
            state.results = res.data
        } catch { state.results = [] }
        finally  { state.loading = false }
    }, 300)
}

// ── Pre-registration lookup ───────────────────────────────────────────────────
const refCode       = ref('')
const lookupLoading = ref(false)
const lookupError   = ref('')
const preRegData    = ref(null)

const lookupByCode = async () => {
    if (!refCode.value.trim()) return
    lookupLoading.value = true
    lookupError.value   = ''
    preRegData.value    = null

    try {
        const res = await axios.get(route('pre-register.lookup'), {
            params: { code: refCode.value.trim().toUpperCase() }
        })

        if (res.data.found) {
            preRegData.value = res.data

            if (res.data.is_group) {
                mode.value = 'group'
                members.value = res.data.members.map(m => ({
                    ...blankMember(),
                    first_name:       m.first_name       ?? '',
                    last_name:        m.last_name        ?? '',
                    municipality:     m.municipality     ?? '',
                    province:         m.province         ?? '',
                    purpose:          m.purpose          ?? '',
                    duration_of_stay: m.duration_of_stay ?? '',
                    contact_number:   m.contact_number   ?? '',
                    visitor_category: m.visitor_category ?? '',
                    // Pre-fill destinations if they selected some during pre-registration
                    destinations:     m.destinations     ?? [],
                    visit_id:         m.visit_id,
                    reference_code:   m.reference_code,
                }))
            } else {
                mode.value = 'single'
                const v = res.data.visit
                form.first_name       = v.first_name       ?? ''
                form.last_name        = v.last_name        ?? ''
                form.municipality     = v.municipality     ?? ''
                form.province         = v.province         ?? ''
                form.contact_number   = v.contact_number   ?? ''
                form.purpose          = v.purpose          ?? ''
                form.duration_of_stay = v.duration_of_stay ?? ''
                form.visitor_category = v.visitor_category ?? ''
                form.destinations     = v.destinations     ?? []
                form.visit_id         = v.visit_id
                form.profile_id       = ''
            }
        }
    } catch (err) {
        lookupError.value = err.response?.data?.message
            ?? 'No pending pre-registration found for this code.'
    } finally {
        lookupLoading.value = false
    }
}

const clearLookup = () => {
    preRegData.value  = null
    refCode.value     = ''
    lookupError.value = ''
    form.reset()
    members.value = [blankMember()]
}

// ═══════════════════════════════════════════════════════════════════════════════
// SINGLE REGISTRATION
// ═══════════════════════════════════════════════════════════════════════════════
const singleSearch = ref(makeSearchState())
const openPurpose  = ref(false)
const openDuration = ref(false)
const openCategory = ref(false)

const form = useForm({
    first_name:       '',
    last_name:        '',
    municipality:     '',
    province:         '',
    place_of_origin:  '',
    purpose:          '',
    purpose_other:    '',
    duration_of_stay: '',
    contact_number:   '',
    visitor_category: '',
    destinations:     [],   // ← NEW: Array<{ attraction_id, other_destination }>
    profile_id:       '',
    visit_id:         '',
})

const onSingleSearch = () => runProfileSearch(singleSearch.value)

const selectSingleProfile = (profile) => {
    singleSearch.value.selected = profile
    singleSearch.value.results  = []
    singleSearch.value.query    = profile.full_name
    form.first_name   = profile.full_name.split(' ')[0]  ?? ''
    form.last_name    = profile.full_name.split(' ').slice(1).join(' ') ?? ''
    form.municipality = profile.municipality ?? ''
    form.province     = profile.province     ?? ''
    form.profile_id   = profile.id
}

const clearSingleProfile = () => {
    singleSearch.value = makeSearchState()
    if (!preRegData.value) form.reset()
    else form.profile_id = ''
}

const categoryLabel = (cat) => cat?.age_range
    ? `${cat.category} (${cat.age_range}) — ₱${cat.fee}`
    : `${cat?.category ?? ''} — ₱${cat?.fee ?? ''}`

const submitSingle = () => {
    form.place_of_origin = `${form.municipality}, ${form.province}`
    form.post(route('registration.store'))
}

// ═══════════════════════════════════════════════════════════════════════════════
// GROUP REGISTRATION
// ═══════════════════════════════════════════════════════════════════════════════
const blankMember = () => ({
    first_name:       '',
    last_name:        '',
    municipality:     '',
    province:         '',
    purpose:          '',
    purpose_other:    '',
    duration_of_stay: '',
    contact_number:   '',
    visitor_category: '',
    destinations:     [],    // ← NEW
    profile_id:       '',
    visit_id:         '',
    reference_code:   '',
    openPurpose:      false,
    openDuration:     false,
    openCategory:     false,
    search:           makeSearchState(),
})

const members     = ref([blankMember()])
const groupForm   = useForm({ members: [] })
const memberCount = computed(() => members.value.length)

const addMember    = () => members.value.push(blankMember())
const removeMember = (i) => { if (members.value.length > 1) members.value.splice(i, 1) }

const cloneFromLeader = (index) => {
    const leader = members.value[0]
    const m      = members.value[index]
    m.municipality     = leader.municipality
    m.province         = leader.province
    m.purpose          = leader.purpose
    m.duration_of_stay = leader.duration_of_stay
    // destinations are NOT cloned — each member may visit different places
}

const onMemberSearch      = (index) => runProfileSearch(members.value[index].search)
const selectMemberProfile = (index, profile) => {
    const m = members.value[index]
    m.search.selected = profile
    m.search.results  = []
    m.search.query    = profile.full_name
    m.first_name      = profile.full_name.split(' ')[0] ?? ''
    m.last_name       = profile.full_name.split(' ').slice(1).join(' ') ?? ''
    m.municipality    = profile.municipality ?? ''
    m.province        = profile.province     ?? ''
    m.profile_id      = profile.id
}
const clearMemberProfile = (index) => {
    const m = members.value[index]
    m.search     = makeSearchState()
    m.first_name = m.last_name = m.municipality = m.province = m.profile_id = ''
}

const submitGroup = () => {
    groupForm.members = members.value.map(m => ({
        first_name:       m.first_name,
        last_name:        m.last_name,
        municipality:     m.municipality,
        province:         m.province,
        place_of_origin:  `${m.municipality}, ${m.province}`,
        purpose:          m.purpose,
        purpose_other:    m.purpose === 'Other' ? m.purpose_other : '',
        duration_of_stay: m.duration_of_stay,
        visitor_category: m.visitor_category,
        destinations:     m.destinations,     // ← NEW
        contact_number:   m.contact_number || '',
        profile_id:       m.profile_id     || '',
        visit_id:         m.visit_id       || '',
    }))
    groupForm.post(route('registration.group'))
}

const preRegBannerText = computed(() => {
    if (!preRegData.value) return ''
    if (preRegData.value.is_group)
        return `Group pre-registration found — ${preRegData.value.members.length} member(s) pre-filled below.`
    return `Pre-registration found — details pre-filled. Verify with visitor and click Next.`
})
</script>

<template>
    <LandingLayout>

        <!-- Top Bar -->
        <div class="container mx-auto px-2">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">

                <div class="relative flex-1">
                    <input v-model="search" type="text"
                        placeholder="Search..."
                        :class="[
                            'w-full p-2 pl-8 rounded-lg border text-sm transition-colors duration-200',
                            search
                                ? 'border-gray-800 bg-white ring-1 ring-gray-800'
                                : 'border-gray-300 bg-white focus:border-gray-400'
                        ]" />
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

        <div class="py-8 px-4">

            <!-- Header -->
            <div class="text-center mb-6">
                <h1 class="font-heading text-gray-800 text-3xl">Tourist Registration</h1>
                <p class="text-sm text-gray-500 mt-1">Enter the details to get going</p>
            </div>

            <!-- Step Indicator -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center mb-8 gap-4 sm:gap-0 px-2">
                <!-- Step 1 -->
                <div class="flex items-center gap-2 justify-center sm:justify-start">

                    <span
                        class="bg-gray-800 text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">
                        1
                    </span>
                    <span class="text-gray-800 font-medium text-sm">
                        General Details
                    </span>
                </div>

                <div class="hidden sm:block w-16 h-px bg-gray-300 mx-3"></div>
                <div class="flex items-center gap-2 justify-center sm:justify-start">
                    <span
                        class="bg-gray-200 text-gray-500 text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">
                        2
                    </span>
                    <span class="text-gray-400 font-medium text-sm">
                        Payment
                    </span>
                </div>

                <div class="hidden sm:block w-16 h-px bg-gray-300 mx-3"></div>

                <!-- Step 3 -->
                <div class="flex items-center gap-2 justify-center sm:justify-start">
                    <span
                        class="bg-gray-200 text-gray-500 text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">
                        3
                    </span>
                    <span class="text-gray-400 font-medium text-sm">
                        Receipt
                    </span>
                </div>

            </div>


            <!-- PRE-REGISTRATION LOOKUP -->
            <div class="max-w-2xl mx-auto mb-4">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                    <p class="text-sm font-semibold text-gray-700 mb-3">
                        Pre-Registration Code
                        <span class="text-gray-400 font-normal ml-1">(enter code if visitor pre-registered online)</span>
                    </p>

                    <div v-if="preRegData && !preRegData.is_group"
                        class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm mb-0">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-mono font-bold text-green-800">{{ preRegData.visit.reference_code }}</span>
                            <span class="text-green-600 text-xs">· {{ preRegData.visit.first_name }} {{ preRegData.visit.last_name }} · {{ preRegData.visit.created_at }}</span>
                        </div>
                        <button type="button" @click="clearLookup" class="text-green-400 hover:text-red-500 text-xs font-bold ml-4">✕ Clear</button>
                    </div>

                    <div v-else-if="preRegData && preRegData.is_group"
                        class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm mb-0">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-bold text-green-800">Group pre-registration found</span>
                            <span class="text-green-600 text-xs">· {{ preRegData.members.length }} member(s) — switched to Group mode</span>
                        </div>
                        <button type="button" @click="clearLookup" class="text-green-400 hover:text-red-500 text-xs font-bold ml-4">✕ Clear</button>
                    </div>

                    <div v-if="!preRegData" class="flex flex-col sm:flex-row gap-2">
                        <input v-model="refCode" type="text" placeholder="e.g. BEL-482951"
                            class="flex-1 w-full border border-gray-200 rounded-xl py-2.5 px-4 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-gray-300"
                            @keyup.enter.prevent="lookupByCode" />
                        <button type="button" @click="lookupByCode"
                            :disabled="lookupLoading || !refCode.trim()"
                            class="w-full sm:w-auto bg-gray-900 text-white text-sm font-bold px-5 py-2.5 rounded-xl disabled:opacity-50 hover:bg-gray-700 transition">
                            {{ lookupLoading ? 'Searching...' : 'Find' }}
                        </button>
                    </div>
                    <p v-if="lookupError" class="text-red-500 text-xs mt-2">{{ lookupError }}</p>
                    <p v-if="!preRegData" class="text-gray-400 text-xs mt-2">Skip if the visitor did not pre-register.</p>
                </div>
            </div>

            <!-- Mode Toggle -->
            <div class="max-w-2xl mx-auto mb-6">
                <div class="flex gap-1 bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
                    <button type="button" @click="mode = 'single'"
                        :disabled="preRegData?.is_group"
                        :class="mode === 'single' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                        class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        Individual
                    </button>
                    <button type="button" @click="mode = 'group'"
                        :disabled="preRegData?.is_group"
                        :class="mode === 'group' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                        class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        Group
                        <span v-if="mode === 'group'" class="ml-1.5 text-xs font-bold bg-white text-gray-900 px-1.5 py-0.5 rounded-full">
                            {{ memberCount }}
                        </span>
                    </button>
                </div>
                <p v-if="preRegData?.is_group" class="text-xs text-gray-400 mt-1.5 text-center">Group mode locked — pre-registration loaded</p>
                <p v-else-if="preRegData && !preRegData.is_group" class="text-xs text-gray-400 mt-1.5 text-center">Individual mode locked — pre-registration loaded</p>
            </div>

            <!-- ════════════════════════════ SINGLE ════════════════════════════ -->
            <div v-if="mode === 'single'" class="max-w-2xl mx-auto">
                <form @submit.prevent="submitSingle"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">

                    <fieldset :disabled="!can('edit_registration')" class="space-y-4">

                    <div v-if="form.errors.error" class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm">
                        {{ form.errors.error }}
                    </div>

                    <!-- Pre-reg banner -->
                    <div v-if="preRegData && !preRegData.is_group"
                        class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                        </svg>
                        Details pre-filled from pre-registration. Verify with visitor and edit if needed.
                    </div>

                    <!-- Returning Visitor Search -->
                    <div v-if="!preRegData">
                        <label class="block text-gray-700 text-sm font-semibold mb-1.5">
                            Returning Visitor?
                            <span class="text-gray-400 font-normal ml-1">(optional — search by name or contact)</span>
                        </label>
                        <div v-if="singleSearch.selected"
                            class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm">
                            <div>
                                <span class="font-semibold text-blue-800">{{ singleSearch.selected.full_name }}</span>
                                <span class="text-blue-400 text-xs ml-2">
                                    {{ singleSearch.selected.visit_count }} visit(s)
                                    <span v-if="singleSearch.selected.last_visit"> · Last: {{ singleSearch.selected.last_visit }}</span>
                                </span>
                            </div>
                            <button type="button" @click="clearSingleProfile" class="text-blue-400 hover:text-red-500 text-xs font-bold ml-4">✕ Clear</button>
                        </div>
                        <div v-if="!singleSearch.selected" class="relative">
                            <input v-model="singleSearch.query" @input="onSingleSearch" type="text"
                                placeholder="Type name or contact number..."
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" />
                            <span v-if="singleSearch.loading" class="absolute right-4 top-2.5 text-xs text-gray-400">Searching...</span>
                            <ul v-if="singleSearch.results.length"
                                class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-auto">
                                <li v-for="p in singleSearch.results" :key="p.id"
                                    @click="selectSingleProfile(p)"
                                    class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b last:border-0">
                                    <div class="text-sm font-semibold text-gray-800">{{ p.full_name }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5 flex gap-3 flex-wrap">
                                        <span>{{ p.contact_number ?? 'No contact' }}</span>
                                        <span>{{ p.place_of_origin }}</span>
                                        <span class="text-blue-500">{{ p.visit_count }} visit(s)</span>
                                        <span v-if="p.last_visit">Last: {{ p.last_visit }}</span>
                                    </div>
                                </li>
                            </ul>
                            <p v-if="singleSearch.query.length >= 2 && !singleSearch.loading && !singleSearch.results.length"
                                class="text-xs text-gray-400 mt-1.5 pl-1">No existing profile — a new profile will be created.</p>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">First Name</label>
                            <input v-model="form.first_name" :disabled="!!singleSearch.selected"
                                :class="singleSearch.selected ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'bg-white'"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="First name" />
                            <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">Last Name</label>
                            <input v-model="form.last_name" :disabled="!!singleSearch.selected"
                                :class="singleSearch.selected ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'bg-white'"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Last name" />
                            <p v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</p>
                        </div>
                    </div>

                    <!-- Place of Origin -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-1.5">
                            Place of Origin
                            <span v-if="singleSearch.selected || preRegData" class="text-blue-400 font-normal text-xs ml-1">(pre-filled — edit if changed)</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-gray-400 text-xs mb-1">Municipality</label>
                                <input v-model="form.municipality" class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Municipality" />
                                <p v-if="form.errors.municipality" class="text-red-500 text-xs mt-1">{{ form.errors.municipality }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs mb-1">Province</label>
                                <input v-model="form.province" class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Province" />
                                <p v-if="form.errors.province" class="text-red-500 text-xs mt-1">{{ form.errors.province }}</p>
                            </div>
                        </div>
                        <p v-if="form.municipality || form.province" class="text-xs text-gray-400 mt-1.5 pl-1">
                            Saved as: <span class="font-mono text-gray-600">{{ form.municipality }}, {{ form.province }}</span>
                        </p>
                    </div>

                    <!-- Contact -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-1.5">
                            Contact Number <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input v-model="form.contact_number" class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="09xxxxxxxxx" />
                    </div>

                    <!-- Visitor Category -->
                    <div class="relative">
                        <label class="block text-gray-700 text-sm font-semibold mb-1.5">
                            Visitor Category <span class="text-red-500">*</span>
                        </label>
                        <button type="button" @click="openCategory = !openCategory"
                            class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 flex items-center justify-between">
                            <span :class="form.visitor_category ? 'text-gray-800' : 'text-gray-400'">
                                {{ form.visitor_category
                                    ? categoryLabel(feeCategories.find(c => c.category === form.visitor_category))
                                    : 'Select visitor category' }}
                            </span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul v-show="openCategory" class="absolute z-20 w-full mt-1 border border-gray-200 rounded-xl bg-white shadow-lg max-h-52 overflow-auto">
                            <li v-for="cat in feeCategories" :key="cat.id"
                                @click="form.visitor_category = cat.category; openCategory = false"
                                class="px-4 py-3 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0 flex items-center justify-between"
                                :class="form.visitor_category === cat.category ? 'bg-gray-50 font-semibold' : ''">
                                <div>
                                    <span class="font-medium">{{ cat.category }}</span>
                                    <span v-if="cat.age_range" class="text-gray-400 text-xs ml-2">{{ cat.age_range }}</span>
                                </div>
                                <span class="text-green-700 font-bold text-xs ml-4">₱{{ cat.fee }}</span>
                            </li>
                        </ul>
                        <p v-if="form.errors.visitor_category" class="text-red-500 text-xs mt-1">{{ form.errors.visitor_category }}</p>
                        <div v-if="form.visitor_category" class="mt-2">
                            <span class="inline-flex items-center gap-1.5 bg-green-50 border border-green-200 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                                Fee per visitor: ₱{{ feeCategories.find(c => c.category === form.visitor_category)?.fee ?? '—' }}
                            </span>
                        </div>
                    </div>

                    <!-- ── NEW: Destination ────────────────────────────────────── -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Destination(s)
                            <span class="text-gray-400 font-normal ml-1">(optional — check all that apply)</span>
                        </label>
                        <DestinationChecklist
                            v-model="form.destinations"
                            :attractions="barangayAttractions"
                        />
                    </div>

                    <!-- Purpose & Duration -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="relative">
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">Purpose of Visit</label>
                            <button type="button" @click="openPurpose = !openPurpose"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                <span :class="form.purpose ? 'text-gray-800' : 'text-gray-400'">{{ form.purpose || 'Select purpose' }}</span>
                            </button>
                            <ul v-show="openPurpose" class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-52 overflow-auto">
                                <li v-for="opt in purposeOptions" :key="opt"
                                    @click="form.purpose = opt; openPurpose = false"
                                    class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0">{{ opt }}</li>
                            </ul>
                            <div v-if="form.purpose === 'Other'" class="mt-2">
                                <input v-model="form.purpose_other" class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Please specify purpose..." />
                            </div>
                            <p v-if="form.errors.purpose" class="text-red-500 text-xs mt-1">{{ form.errors.purpose }}</p>
                        </div>
                        <div class="relative">
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">Duration of Stay</label>
                            <button type="button" @click="openDuration = !openDuration"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                <span :class="form.duration_of_stay ? 'text-gray-800' : 'text-gray-400'">{{ form.duration_of_stay || 'Select duration' }}</span>
                            </button>
                            <ul v-show="openDuration" class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-52 overflow-auto">
                                <li v-for="opt in durationOptions" :key="opt"
                                    @click="form.duration_of_stay = opt; openDuration = false"
                                    class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0">{{ opt }}</li>
                            </ul>
                            <p v-if="form.errors.duration_of_stay" class="text-red-500 text-xs mt-1">{{ form.errors.duration_of_stay }}</p>
                        </div>
                    </div>

                    <div class="flex justify-center pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="bg-gray-900 text-white font-bold py-2.5 px-10 rounded-lg disabled:opacity-50 text-sm hover:bg-gray-700 transition"
                            :title="!can('edit_registration') ? 'You do not have permission to edit registrations' : ''">
                            {{ form.processing ? 'Saving...' : 'Next →' }}
                        </button>
                    </div>

                    </fieldset>
                </form>
            </div>

            <!-- ════════════════════════════ GROUP ════════════════════════════ -->
            <div v-if="mode === 'group'" class="max-w-2xl mx-auto space-y-4">
                <form>
                    <fieldset :disabled="!can('edit_registration')" class="space-y-4">

                    <div v-if="groupForm.errors.error" class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm">{{ groupForm.errors.error }}</div>

                    <!-- Group pre-reg banner -->
                    <div v-if="preRegData?.is_group" class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                        </svg>
                        {{ preRegData.members.length }} member(s) pre-filled from group pre-registration. Verify each member and click Register.
                    </div>

                    <!-- Member cards -->
                    <div v-for="(m, i) in members" :key="i"
                        class="bg-white rounded-2xl border shadow-sm overflow-visible"
                        :class="i === 0 ? 'border-gray-800' : 'border-gray-200'">

                        <!-- Card Header -->
                        <div class="flex items-center justify-between px-5 py-3 rounded-t-2xl"
                            :class="i === 0 ? 'bg-gray-900' : 'bg-gray-50 border-b border-gray-100'">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full"
                                    :class="i === 0 ? 'bg-white text-gray-900' : 'bg-gray-200 text-gray-600'">
                                    {{ i === 0 ? '★ Group Leader' : `Member ${i + 1}` }}
                                </span>
                                <span v-if="m.reference_code" class="font-mono text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">{{ m.reference_code }}</span>
                                <span v-else-if="m.search.selected" class="text-xs text-blue-400">Returning visitor</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button v-if="i > 0 && !preRegData?.is_group" type="button" @click="cloneFromLeader(i)"
                                    :disabled="!members[0].municipality"
                                    class="text-xs font-semibold px-3 py-1 rounded-lg border transition"
                                    :class="members[0].municipality ? 'border-blue-300 text-blue-600 hover:bg-blue-50' : 'border-gray-200 text-gray-300 cursor-not-allowed'">
                                    ↓ Clone from Leader
                                </button>
                                <button v-if="i > 0 && !preRegData?.is_group" type="button" @click="removeMember(i)" class="text-xs text-red-400 hover:text-red-600 font-semibold">Remove</button>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 space-y-5">

                            <!-- Profile search -->
                            <div v-if="!m.reference_code">
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">Returning visitor? <span class="text-gray-400 font-normal">(optional)</span></label>
                                <div v-if="m.search.selected" class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5 text-xs">
                                    <div>
                                        <span class="font-semibold text-blue-800">{{ m.search.selected.full_name }}</span>
                                        <span class="text-blue-400 ml-2">{{ m.search.selected.visit_count }} visit(s)</span>
                                    </div>
                                    <button type="button" @click="clearMemberProfile(i)" class="text-blue-400 hover:text-red-500 font-bold ml-3">✕</button>
                                </div>
                                <div v-if="!m.search.selected" class="relative">
                                    <input v-model="m.search.query" @input="onMemberSearch(i)" type="text" placeholder="Search name or contact..."
                                        class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" />
                                    <span v-if="m.search.loading" class="absolute right-4 top-2.5 text-xs text-gray-400">Searching...</span>
                                    <ul v-if="m.search.results.length" class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-44 overflow-auto">
                                        <li v-for="p in m.search.results" :key="p.id" @click="selectMemberProfile(i, p)"
                                            class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm border-b last:border-0">
                                            <span class="font-semibold text-gray-800">{{ p.full_name }}</span>
                                            <span class="text-gray-400 text-xs ml-2">{{ p.contact_number }}</span>
                                            <span class="text-blue-400 text-xs ml-2">{{ p.visit_count }} visit(s)</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-gray-600 text-xs font-semibold mb-1.5">First Name</label>
                                    <input v-model="m.first_name" :disabled="!!m.search.selected"
                                        :class="m.search.selected ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'bg-white'"
                                        class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="First name" />
                                </div>
                                <div>
                                    <label class="block text-gray-600 text-xs font-semibold mb-1.5">Last Name</label>
                                    <input v-model="m.last_name" :disabled="!!m.search.selected"
                                        :class="m.search.selected ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'bg-white'"
                                        class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Last name" />
                                </div>
                            </div>

                            <!-- Origin -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-gray-600 text-xs font-semibold mb-1.5">Municipality</label>
                                    <input v-model="m.municipality" class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Municipality" />
                                </div>
                                <div>
                                    <label class="block text-gray-600 text-xs font-semibold mb-1.5">Province</label>
                                    <input v-model="m.province" class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Province" />
                                </div>
                            </div>

                            <!-- Contact -->
                            <div>
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">Contact <span class="text-gray-400 font-normal">(optional)</span></label>
                                <input v-model="m.contact_number" class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="09xxxxxxxxx" />
                            </div>

                            <!-- Visitor Category per member -->
                            <div class="relative">
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">Visitor Category <span class="text-red-500">*</span></label>
                                <button type="button" @click="m.openCategory = !m.openCategory"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none flex items-center justify-between">
                                    <span :class="m.visitor_category ? 'text-gray-800' : 'text-gray-400'">
                                        {{ m.visitor_category
                                            ? categoryLabel(feeCategories.find(c => c.category === m.visitor_category))
                                            : 'Select category' }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul v-show="m.openCategory" class="absolute z-20 w-full mt-1 border border-gray-200 rounded-xl bg-white shadow-lg max-h-44 overflow-auto">
                                    <li v-for="cat in feeCategories" :key="cat.id"
                                        @click="m.visitor_category = cat.category; m.openCategory = false"
                                        class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0 flex items-center justify-between"
                                        :class="m.visitor_category === cat.category ? 'bg-gray-50 font-semibold' : ''">
                                        <div>
                                            <span class="font-medium">{{ cat.category }}</span>
                                            <span v-if="cat.age_range" class="text-gray-400 text-xs ml-2">{{ cat.age_range }}</span>
                                        </div>
                                        <span class="text-green-700 font-bold text-xs ml-4">₱{{ cat.fee }}</span>
                                    </li>
                                </ul>
                                <div v-if="m.visitor_category" class="mt-1.5">
                                    <span class="inline-flex items-center gap-1 bg-green-50 border border-green-200 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        ₱{{ feeCategories.find(c => c.category === m.visitor_category)?.fee ?? '—' }} / visitor
                                    </span>
                                </div>
                            </div>

                            <!-- ── NEW: Destination per member ──────────────── -->
                            <div>
                                <label class="block text-gray-600 text-xs font-semibold mb-2">
                                    Destination(s) <span class="text-gray-400 font-normal">(optional)</span>
                                </label>
                                <DestinationChecklist
                                    v-model="m.destinations"
                                    :attractions="barangayAttractions"
                                />
                            </div>

                            <!-- Purpose & Duration -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div class="relative">
                                    <label class="block text-gray-600 text-xs font-semibold mb-1.5">Purpose of Visit</label>
                                    <button type="button" @click="m.openPurpose = !m.openPurpose"
                                        class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none">
                                        <span :class="m.purpose ? 'text-gray-800' : 'text-gray-400'">{{ m.purpose || 'Select purpose' }}</span>
                                    </button>
                                    <ul v-show="m.openPurpose" class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-44 overflow-auto">
                                        <li v-for="opt in purposeOptions" :key="opt"
                                            @click="m.purpose = opt; m.openPurpose = false"
                                            class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0">{{ opt }}</li>
                                    </ul>
                                    <div v-if="m.purpose === 'Other'" class="mt-2">
                                        <input v-model="m.purpose_other" class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Please specify purpose..." />
                                    </div>
                                </div>
                                <div class="relative">
                                    <label class="block text-gray-600 text-xs font-semibold mb-1.5">Duration of Stay</label>
                                    <button type="button" @click="m.openDuration = !m.openDuration"
                                        class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none">
                                        <span :class="m.duration_of_stay ? 'text-gray-800' : 'text-gray-400'">{{ m.duration_of_stay || 'Select duration' }}</span>
                                    </button>
                                    <ul v-show="m.openDuration" class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-44 overflow-auto">
                                        <li v-for="opt in durationOptions" :key="opt"
                                            @click="m.duration_of_stay = opt; m.openDuration = false"
                                            class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0">{{ opt }}</li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Add Member -->
                    <button v-if="!preRegData?.is_group" type="button" @click="addMember"
                        class="w-full py-4 border-2 border-dashed border-gray-300 rounded-2xl text-sm text-gray-500 hover:border-gray-500 hover:text-gray-700 transition font-semibold">
                        + Add Member
                    </button>

                    <!-- Summary & Submit -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-800 font-semibold">{{ memberCount }} visitor(s) in this group</p>
                            <p class="text-xs text-gray-400 mt-0.5">Payment will be collected for each member one by one.</p>
                        </div>
                        <button type="button" @click="submitGroup" :disabled="groupForm.processing"
                            class="bg-gray-900 text-white font-bold py-2.5 px-6 rounded-lg disabled:opacity-50 ml-4 whitespace-nowrap text-sm hover:bg-gray-700 transition"
                            :title="!can('edit_registration') ? 'You do not have permission to edit registrations' : ''">
                            {{ groupForm.processing ? 'Registering...' : `Register ${memberCount} Visitor(s) →` }}
                        </button>
                    </div>

                    </fieldset>
                </form>
            </div>
        </div>
    </LandingLayout>
</template>