<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'
import axios from 'axios'

// ── Mode toggle ───────────────────────────────────────────────────────────────
const mode = ref('single') // 'single' | 'group'

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
            const res = await axios.get(route('visitors.search-profile'), {
                params: { query: state.query }
            })
            state.results = res.data
        } catch { state.results = [] }
        finally  { state.loading = false }
    }, 300)
}

// ═══════════════════════════════════════════════════════════════════════════════
// PRE-REGISTRATION LOOKUP
// Staff enters the visitor's reference code → pulls their pre-submitted data.
// Calls GET /pre-register/lookup?code=... → PublicRegController::lookup()
// If the code is a group code, returns all members and switches to group mode.
// ═══════════════════════════════════════════════════════════════════════════════
const refCode       = ref('')
const lookupLoading = ref(false)
const lookupError   = ref('')
const preRegData    = ref(null)   // { found, is_group, visit, members }

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
                // Switch to group mode and pre-fill all members
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
                    visit_id:         m.visit_id,          // ties to existing visit
                    reference_code:   m.reference_code,
                }))
            } else {
                // Single — pre-fill single form
                mode.value = 'single'
                const v = res.data.visit
                form.first_name       = v.first_name       ?? ''
                form.last_name        = v.last_name        ?? ''
                form.municipality     = v.municipality     ?? ''
                form.province         = v.province         ?? ''
                form.contact_number   = v.contact_number   ?? ''
                form.purpose          = v.purpose          ?? ''
                form.duration_of_stay = v.duration_of_stay ?? ''
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
    profile_id:       '',
    visit_id:         '',   // set when pre-registered visit is found
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
    // Only clear profile-linked fields, keep pre-reg data if present
    if (!preRegData.value) form.reset()
    else form.profile_id = ''
}

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
    profile_id:       '',
    visit_id:         '',           // set when pre-reg member is found
    reference_code:   '',           // shown in badge when pre-reg found
    openPurpose:      false,
    openDuration:     false,
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
}

const onMemberSearch  = (index) => runProfileSearch(members.value[index].search)

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
        contact_number:   m.contact_number || '',
        profile_id:       m.profile_id     || '',
        visit_id:         m.visit_id       || '',   // pre-reg: update existing
    }))
    groupForm.post(route('registration.group'))
}

// ── Pre-reg banner helper ─────────────────────────────────────────────────────
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

        <div class="py-8 px-4">

            <!-- Header -->
            <div class="text-center mb-6">
                <h1 class="font-heading text-gray-800 text-3xl">Tourist Registration</h1>
                <p class="text-sm text-gray-500 mt-1">Enter the details to get going</p>
            </div>

            <!-- Step Indicator -->
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center gap-2">
                    <span class="bg-gray-800 text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">1</span>
                    <span class="text-gray-800 font-medium text-sm">General Details</span>
                </div>
                <div class="w-16 h-px bg-gray-300 mx-3"></div>
                <div class="flex items-center gap-2">
                    <span class="bg-gray-200 text-gray-500 text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">2</span>
                    <span class="text-gray-400 font-medium text-sm">Payment</span>
                </div>
                <div class="w-16 h-px bg-gray-300 mx-3"></div>
                <div class="flex items-center gap-2">
                    <span class="bg-gray-200 text-gray-500 text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">3</span>
                    <span class="text-gray-400 font-medium text-sm">Receipt</span>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- PRE-REGISTRATION LOOKUP — always visible at top               -->
            <!-- Staff enters visitor's reference code to pull pre-reg data.   -->
            <!-- If code is a group code → auto-switches to group mode.        -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div class="max-w-2xl mx-auto mb-4">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">

                    <p class="text-sm font-semibold text-gray-700 mb-3">
                        Pre-Registration Code
                        <span class="text-gray-400 font-normal ml-1">
                            (enter code if visitor pre-registered online)
                        </span>
                    </p>

                    <!-- Found badge — single -->
                    <div v-if="preRegData && !preRegData.is_group"
                        class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm mb-0">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-mono font-bold text-green-800">{{ preRegData.visit.reference_code }}</span>
                            <span class="text-green-600 text-xs">
                                · {{ preRegData.visit.first_name }} {{ preRegData.visit.last_name }}
                                · {{ preRegData.visit.created_at }}
                            </span>
                        </div>
                        <button type="button" @click="clearLookup"
                            class="text-green-400 hover:text-red-500 text-xs font-bold ml-4">✕ Clear</button>
                    </div>

                    <!-- Found badge — group -->
                    <div v-else-if="preRegData && preRegData.is_group"
                        class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm mb-0">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-bold text-green-800">Group pre-registration found</span>
                            <span class="text-green-600 text-xs">
                                · {{ preRegData.members.length }} member(s) — switched to Group mode
                            </span>
                        </div>
                        <button type="button" @click="clearLookup"
                            class="text-green-400 hover:text-red-500 text-xs font-bold ml-4">✕ Clear</button>
                    </div>

                    <!-- Search input — hidden once found -->
                    <div v-if="!preRegData" class="flex gap-2">
                        <input
                            v-model="refCode"
                            type="text"
                            placeholder="e.g. BEL-482951"
                            class="flex-1 border border-gray-200 rounded-xl py-2.5 px-4 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-gray-300"
                            @keyup.enter.prevent="lookupByCode"
                        />
                        <button
                            type="button"
                            @click="lookupByCode"
                            :disabled="lookupLoading || !refCode.trim()"
                            class="bg-gray-900 text-white text-sm font-bold px-5 py-2.5 rounded-xl disabled:opacity-50 hover:bg-gray-700 transition">
                            {{ lookupLoading ? 'Searching...' : 'Find' }}
                        </button>
                    </div>
                    <p v-if="lookupError" class="text-red-500 text-xs mt-2">{{ lookupError }}</p>
                    <p v-if="!preRegData" class="text-gray-400 text-xs mt-2">
                        Skip if the visitor did not pre-register.
                    </p>
                </div>
            </div>

            <!-- Mode Toggle — disabled when group pre-reg is loaded -->
            <div class="max-w-2xl mx-auto mb-6">
                <div class="flex gap-1 bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
                    <button type="button" @click="mode = 'single'"
                        :disabled="preRegData?.is_group"
                        :class="mode === 'single' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                        class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        Individual
                    </button>
                    <button type="button" @click="mode = 'group'"
                        :disabled="preRegData && !preRegData.is_group"
                        :class="mode === 'group' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                        class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        Group
                        <span v-if="mode === 'group'"
                            class="ml-1.5 text-xs font-bold bg-white text-gray-900 px-1.5 py-0.5 rounded-full">
                            {{ memberCount }}
                        </span>
                    </button>
                </div>
                <p v-if="preRegData?.is_group" class="text-xs text-gray-400 mt-1.5 text-center">
                    Group mode locked — pre-registration loaded
                </p>
                <p v-else-if="preRegData && !preRegData.is_group" class="text-xs text-gray-400 mt-1.5 text-center">
                    Individual mode locked — pre-registration loaded
                </p>
            </div>

            <!-- ════════════════════════════════════════════ -->
            <!-- SINGLE                                       -->
            <!-- ════════════════════════════════════════════ -->
            <div v-if="mode === 'single'" class="max-w-2xl mx-auto">
                <form @submit.prevent="submitSingle"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">

                    <div v-if="form.errors.error"
                        class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm">
                        {{ form.errors.error }}
                    </div>

                    <!-- Pre-reg info banner -->
                    <div v-if="preRegData && !preRegData.is_group"
                        class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                        </svg>
                        Details pre-filled from pre-registration. Verify with visitor and edit if needed.
                    </div>

                    <!-- Returning Visitor Search — hidden when pre-reg loaded -->
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
                            <button type="button" @click="clearSingleProfile"
                                class="text-blue-400 hover:text-red-500 text-xs font-bold ml-4">✕ Clear</button>
                        </div>

                        <div v-if="!singleSearch.selected" class="relative">
                            <input v-model="singleSearch.query" @input="onSingleSearch"
                                type="text" placeholder="Type name or contact number..."
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" />
                            <span v-if="singleSearch.loading"
                                class="absolute right-4 top-2.5 text-xs text-gray-400">Searching...</span>
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
                                class="text-xs text-gray-400 mt-1.5 pl-1">
                                No existing profile — a new profile will be created.
                            </p>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">First Name</label>
                            <input v-model="form.first_name"
                                :disabled="!!singleSearch.selected"
                                :class="singleSearch.selected ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'bg-white'"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                placeholder="First name" />
                            <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">Last Name</label>
                            <input v-model="form.last_name"
                                :disabled="!!singleSearch.selected"
                                :class="singleSearch.selected ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'bg-white'"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                placeholder="Last name" />
                            <p v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</p>
                        </div>
                    </div>

                    <!-- Place of Origin -->
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-1.5">
                            Place of Origin
                            <span v-if="singleSearch.selected || preRegData"
                                class="text-blue-400 font-normal text-xs ml-1">(pre-filled — edit if changed)</span>
                        </label>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-gray-400 text-xs mb-1">Municipality</label>
                                <input v-model="form.municipality"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                    placeholder="Municipality" />
                                <p v-if="form.errors.municipality" class="text-red-500 text-xs mt-1">{{ form.errors.municipality }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs mb-1">Province</label>
                                <input v-model="form.province"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                    placeholder="Province" />
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
                        <input v-model="form.contact_number"
                            class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                            placeholder="09xxxxxxxxx" />
                    </div>

                    <!-- Purpose & Duration -->
                    <div class="grid grid-cols-2 gap-5">
                        <div class="relative">
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">Purpose of Visit</label>
                            <button type="button" @click="openPurpose = !openPurpose"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                <span :class="form.purpose ? 'text-gray-800' : 'text-gray-400'">
                                    {{ form.purpose || 'Select purpose' }}
                                </span>
                            </button>
                            <ul v-show="openPurpose"
                                class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-52 overflow-auto">
                                <li v-for="opt in purposeOptions" :key="opt"
                                    @click="form.purpose = opt; openPurpose = false"
                                    class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0">
                                    {{ opt }}
                                </li>
                            </ul>
                            <div v-if="form.purpose === 'Other'" class="mt-2">
                                <input v-model="form.purpose_other"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                    placeholder="Please specify purpose..." />
                            </div>
                            <p v-if="form.errors.purpose" class="text-red-500 text-xs mt-1">{{ form.errors.purpose }}</p>
                        </div>

                        <div class="relative">
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">Duration of Stay</label>
                            <button type="button" @click="openDuration = !openDuration"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                <span :class="form.duration_of_stay ? 'text-gray-800' : 'text-gray-400'">
                                    {{ form.duration_of_stay || 'Select duration' }}
                                </span>
                            </button>
                            <ul v-show="openDuration"
                                class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-52 overflow-auto">
                                <li v-for="opt in durationOptions" :key="opt"
                                    @click="form.duration_of_stay = opt; openDuration = false"
                                    class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0">
                                    {{ opt }}
                                </li>
                            </ul>
                            <p v-if="form.errors.duration_of_stay" class="text-red-500 text-xs mt-1">{{ form.errors.duration_of_stay }}</p>
                        </div>
                    </div>

                    <div class="flex justify-center pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="bg-gray-900 text-white font-bold py-2.5 px-10 rounded-lg disabled:opacity-50 text-sm hover:bg-gray-700 transition">
                            {{ form.processing ? 'Saving...' : 'Next →' }}
                        </button>
                    </div>

                </form>
            </div>

            <!-- ════════════════════════════════════════════ -->
            <!-- GROUP                                        -->
            <!-- ════════════════════════════════════════════ -->
            <div v-if="mode === 'group'" class="max-w-2xl mx-auto space-y-4">

                <div v-if="groupForm.errors.error"
                    class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm">
                    {{ groupForm.errors.error }}
                </div>

                <!-- Group pre-reg banner -->
                <div v-if="preRegData?.is_group"
                    class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                    </svg>
                    {{ preRegData.members.length }} member(s) pre-filled from group pre-registration.
                    Verify each member and click Register.
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
                            <!-- Pre-reg code badge per member -->
                            <span v-if="m.reference_code"
                                class="font-mono text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                {{ m.reference_code }}
                            </span>
                            <span v-else-if="m.search.selected" class="text-xs text-blue-400">Returning visitor</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button v-if="i > 0 && !preRegData?.is_group" type="button"
                                @click="cloneFromLeader(i)"
                                :disabled="!members[0].municipality"
                                class="text-xs font-semibold px-3 py-1 rounded-lg border transition"
                                :class="members[0].municipality
                                    ? 'border-blue-300 text-blue-600 hover:bg-blue-50'
                                    : 'border-gray-200 text-gray-300 cursor-not-allowed'">
                                ↓ Clone from Leader
                            </button>
                            <button v-if="i > 0 && !preRegData?.is_group" type="button"
                                @click="removeMember(i)"
                                class="text-xs text-red-400 hover:text-red-600 font-semibold">
                                Remove
                            </button>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 space-y-5">

                        <!-- Profile search — hidden for pre-reg members -->
                        <div v-if="!m.reference_code">
                            <label class="block text-gray-600 text-xs font-semibold mb-1.5">
                                Returning visitor? <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <div v-if="m.search.selected"
                                class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5 text-xs">
                                <div>
                                    <span class="font-semibold text-blue-800">{{ m.search.selected.full_name }}</span>
                                    <span class="text-blue-400 ml-2">{{ m.search.selected.visit_count }} visit(s)</span>
                                </div>
                                <button type="button" @click="clearMemberProfile(i)"
                                    class="text-blue-400 hover:text-red-500 font-bold ml-3">✕</button>
                            </div>
                            <div v-if="!m.search.selected" class="relative">
                                <input v-model="m.search.query" @input="onMemberSearch(i)"
                                    type="text" placeholder="Search name or contact..."
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" />
                                <span v-if="m.search.loading"
                                    class="absolute right-4 top-2.5 text-xs text-gray-400">Searching...</span>
                                <ul v-if="m.search.results.length"
                                    class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-44 overflow-auto">
                                    <li v-for="p in m.search.results" :key="p.id"
                                        @click="selectMemberProfile(i, p)"
                                        class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm border-b last:border-0">
                                        <span class="font-semibold text-gray-800">{{ p.full_name }}</span>
                                        <span class="text-gray-400 text-xs ml-2">{{ p.contact_number }}</span>
                                        <span class="text-blue-400 text-xs ml-2">{{ p.visit_count }} visit(s)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">First Name</label>
                                <input v-model="m.first_name"
                                    :disabled="!!m.search.selected"
                                    :class="m.search.selected ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'bg-white'"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                    placeholder="First name" />
                            </div>
                            <div>
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">Last Name</label>
                                <input v-model="m.last_name"
                                    :disabled="!!m.search.selected"
                                    :class="m.search.selected ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : 'bg-white'"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                    placeholder="Last name" />
                            </div>
                        </div>

                        <!-- Origin -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">Municipality</label>
                                <input v-model="m.municipality"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                    placeholder="Municipality" />
                            </div>
                            <div>
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">Province</label>
                                <input v-model="m.province"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                    placeholder="Province" />
                            </div>
                        </div>

                        <!-- Contact -->
                        <div>
                            <label class="block text-gray-600 text-xs font-semibold mb-1.5">
                                Contact <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <input v-model="m.contact_number"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                placeholder="09xxxxxxxxx" />
                        </div>

                        <!-- Purpose & Duration -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative">
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">Purpose of Visit</label>
                                <button type="button" @click="m.openPurpose = !m.openPurpose"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none">
                                    <span :class="m.purpose ? 'text-gray-800' : 'text-gray-400'">
                                        {{ m.purpose || 'Select purpose' }}
                                    </span>
                                </button>
                                <ul v-show="m.openPurpose"
                                    class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-44 overflow-auto">
                                    <li v-for="opt in purposeOptions" :key="opt"
                                        @click="m.purpose = opt; m.openPurpose = false"
                                        class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0">
                                        {{ opt }}
                                    </li>
                                </ul>
                                <div v-if="m.purpose === 'Other'" class="mt-2">
                                    <input v-model="m.purpose_other"
                                        class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                        placeholder="Please specify purpose..." />
                                </div>
                            </div>

                            <div class="relative">
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">Duration of Stay</label>
                                <button type="button" @click="m.openDuration = !m.openDuration"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm focus:outline-none">
                                    <span :class="m.duration_of_stay ? 'text-gray-800' : 'text-gray-400'">
                                        {{ m.duration_of_stay || 'Select duration' }}
                                    </span>
                                </button>
                                <ul v-show="m.openDuration"
                                    class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-44 overflow-auto">
                                    <li v-for="opt in durationOptions" :key="opt"
                                        @click="m.duration_of_stay = opt; m.openDuration = false"
                                        class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b last:border-0">
                                        {{ opt }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Add Member — hidden when pre-reg group is loaded -->
                <button v-if="!preRegData?.is_group"
                    type="button" @click="addMember"
                    class="w-full py-4 border-2 border-dashed border-gray-300 rounded-2xl text-sm text-gray-500 hover:border-gray-500 hover:text-gray-700 transition font-semibold">
                    + Add Member
                </button>

                <!-- Summary & Submit -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-800 font-semibold">{{ memberCount }} visitor(s) in this group</p>
                        <p class="text-xs text-gray-400 mt-0.5">Payment will be collected for each member one by one.</p>
                    </div>
                    <button type="button" @click="submitGroup" :disabled="groupForm.processing"
                        class="bg-gray-900 text-white font-bold py-2.5 px-6 rounded-lg disabled:opacity-50 ml-4 whitespace-nowrap text-sm hover:bg-gray-700 transition">
                        {{ groupForm.processing ? 'Registering...' : `Register ${memberCount} Visitor(s) →` }}
                    </button>
                </div>

            </div>
        </div>

    </LandingLayout>
</template>