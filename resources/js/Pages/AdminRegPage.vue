<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'
import DestinationChecklist from '@/Components/DestinationChecklist.vue'
import axios from 'axios'

const page        = usePage()
const permissions = computed(() => page.props.auth?.permissions ?? [])
const userRole    = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase())
const can = (p) => userRole.value === 'admin' || permissions.value.includes(p)
const authUser = computed(() => page.props.auth?.user)
const showUser = ref(false)

const props = defineProps({
    feeCategories:       { type: Array, default: () => [] },
    barangayAttractions: { type: Array, default: () => [] },
    formFields:          { type: Array, default: () => [] },
})

// ── Form field settings ───────────────────────────────────────────────────────
const isVisible  = (key) => props.formFields.find(f => f.field_key === key)?.is_visible  ?? true

// ── Age → visitor_category from DB fee_categories ────────────────────────────
const deriveCategory = (age) => {
    if (!age || isNaN(age)) return ''
    const n = parseInt(age)
    for (const cat of props.feeCategories) {
        const r = cat.age_range?.toLowerCase() ?? ''
        if (r.includes('above') || r.includes('abov')) {
            const min = parseInt(r)
            if (!isNaN(min) && n >= min) return cat.category
        }
        if (r.includes('below')) {
            const max = parseInt(r.replace(/[^\d]/g, ''))
            if (!isNaN(max) && n <= max) return cat.category
        }
        const rangeMatch = r.match(/(\d+)\s*[-–]\s*(\d+)/)
        if (rangeMatch) {
            const [, lo, hi] = rangeMatch.map(Number)
            if (n >= lo && n <= hi) return cat.category
        }
    }
    return ''
}

// ── Nationality auto-detection ────────────────────────────────────────────────
const deriveNationality = (country) => {
    if (!country) return ''
    const c = country.toLowerCase().trim()
    if (c === 'philippines' || c === '' || c === 'ph') return 'Local'
    if (c.includes('aklan') || c.includes('buruanga')) return 'Aklanon'
    return 'Foreign'
}

// ── Address autocomplete ──────────────────────────────────────────────────────
const allAddresses = computed(() => {
    const addrs = new Set()
    members.value.forEach(m => {
        if (m.town_city) addrs.add(m.town_city)
        if (m.country)   addrs.add(m.country)
    })
    return [...addrs]
})

// ── Shared top-section fields (global for the whole form) ─────────────────────
const sharedGender       = ref('')          // M / F — representative's gender
const sharedCategory     = ref('')          // Senior Citizen / Adult / Student PWD / Child
const sharedNationality  = ref('')          // Local / Aklanon / OFW
const sharedCountry      = ref('')          // Foreign checkbox toggle
const sharedAccommodations = ref([])        // DestinationChecklist
const isDayTour          = ref(true)
const nights             = ref('')
const purpose            = ref('')
const purposeOther       = ref('')

const durationLabel = computed(() => {
    if (isDayTour.value) return 'Day Tour'
    return nights.value ? `${nights.value} night(s)` : ''
})

// ── Blank member ──────────────────────────────────────────────────────────────
const blankMember = () => ({
    surname:          '',
    first_name:       '',
    middle_name:      '',
    town_city:        '',
    country:          '',
    sex:              '',
    age:              '',
    visitor_category: '',
    contact_number:   '',
    nationality:      '',
    remarks:          '',
    profile_id:       '',
    visit_id:         '',
    reference_code:   '',
    search:           { query: '', results: [], loading: false, selected: null, timer: null },
    showTownSug:      false,
    showCountrySug:   false,
    _nationalityManuallySet: false,
})

const members = ref([blankMember()])

const isGroup = computed(() =>
    members.value.length > 1 &&
    members.value.slice(1).some(m => m.surname.trim() || m.first_name.trim())
)

const activeMemberCount = computed(() =>
    members.value.filter((m, i) => i === 0 || m.surname.trim() || m.first_name.trim()).length
)

const addRow    = () => members.value.push(blankMember())
const removeRow = (i) => { if (members.value.length > 1) members.value.splice(i, 1) }

// ── Watch age → category per row ──────────────────────────────────────────────
const setupAgeWatch = (i) => {
    watch(() => members.value[i]?.age, (age) => {
        if (members.value[i]) members.value[i].visitor_category = deriveCategory(age)
    })
}
members.value.forEach((_, i) => setupAgeWatch(i))
watch(() => members.value.length, (len) => setupAgeWatch(len - 1))

// ── Watch country → nationality per row ───────────────────────────────────────
watch(() => members.value.map(m => m.country), (countries) => {
    countries.forEach((c, i) => {
        if (members.value[i] && !members.value[i]._nationalityManuallySet) {
            members.value[i].nationality = deriveNationality(c)
        }
    })
}, { deep: true })

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
            const src = res.data.is_group ? res.data.members : [res.data.visit]
            members.value = src.map((v, i) => ({
                ...blankMember(),
                surname:          v.last_name        ?? '',
                first_name:       v.first_name       ?? '',
                middle_name:      v.middle_name      ?? '',
                town_city:        v.town_city        ?? v.municipality ?? '',
                country:          v.country          ?? 'Philippines',
                // Sex per person — stored as 'M' or 'F'
                sex:              v.sex              ?? '',
                // Age must be string for input[type=number] v-model
                age:              v.age != null && v.age !== '' ? String(v.age) : '',
                // visitor_category auto-derived from age but use stored value if available
                visitor_category: v.visitor_category ?? '',
                nationality:      v.nationality      ?? '',
                contact_number:   v.contact_number   ?? '',
                remarks:          v.remarks          ?? '',
                visit_id:         v.visit_id,
                reference_code:   v.reference_code,
                // Prevent nationality watcher from overwriting the pre-filled value
                _nationalityManuallySet: !!(v.nationality),
            }))
            if (src[0]) {
                // Shared fields — set from the first member (representative)
                purpose.value              = src[0].purpose       ?? ''
                purposeOther.value         = src[0].purpose_other ?? ''
                isDayTour.value            = src[0].is_day_tour   ?? true
                nights.value               = src[0].nights        ?? ''
                sharedAccommodations.value = src[0].destinations  ?? []

                // Gender: use the representative's sex
                sharedGender.value      = src[0].sex              ?? ''

                // Category: use the representative's visitor_category
                sharedCategory.value    = src[0].visitor_category ?? ''

                // Nationality: use stored value, or auto-derive from country if null
                const nat = src[0].nationality ?? ''
                if (nat) {
                    sharedNationality.value = nat
                } else if (src[0].country && src[0].country.toLowerCase() !== 'philippines' && src[0].country !== '') {
                    sharedNationality.value = 'Foreign'
                } else {
                    sharedNationality.value = 'Local' // default: Philippines = Local
                }
                // Country checkbox — check Foreign if applicable
                sharedCountry.value = (sharedNationality.value === 'Foreign') ? 'Foreign' : ''
            }

            // Also ensure age is stored as string for v-model on input[type=number]
            members.value = members.value.map(m => ({
                ...m,
                age: m.age !== null && m.age !== undefined && m.age !== '' ? String(m.age) : '',
                _nationalityManuallySet: true, // prevent auto-overwrite from country watcher
            }))
        }
    } catch (err) {
        lookupError.value = err.response?.data?.message ?? 'No pending pre-registration found.'
    } finally {
        lookupLoading.value = false
    }
}

const clearLookup = () => {
    preRegData.value           = null
    refCode.value              = ''
    lookupError.value          = ''
    members.value              = [blankMember()]
    sharedAccommodations.value = []
    sharedGender.value         = ''
    sharedCategory.value       = ''
    sharedNationality.value    = ''
    sharedCountry.value        = ''
    purpose.value              = ''
    purposeOther.value         = ''
    isDayTour.value            = true
    nights.value               = ''
}

// ── Returning visitor search ──────────────────────────────────────────────────
const runSearch = async (member) => {
    clearTimeout(member.search.timer)
    if (member.search.query.length < 2) { member.search.results = []; return }
    member.search.timer = setTimeout(async () => {
        member.search.loading = true
        try {
            const res = await axios.get(route('visitors.search-profile'), {
                params: { query: member.search.query }
            })
            member.search.results = res.data
        } catch { member.search.results = [] }
        finally { member.search.loading = false }
    }, 300)
}

const selectProfile = (member, profile) => {
    member.search.selected = profile
    member.search.results  = []
    member.search.query    = profile.full_name
    const parts = profile.full_name.split(' ')
    member.first_name = parts[0] || ''
    member.surname    = parts.slice(1).join(' ') || ''
    member.town_city  = profile.municipality ?? ''
    member.country    = profile.province ?? ''
    member.profile_id = profile.id
}

const clearProfile = (member) => {
    member.search = { query: '', results: [], loading: false, selected: null, timer: null }
    member.surname = member.first_name = member.middle_name = ''
    member.town_city = member.country = member.profile_id = ''
}

// ── Submit ────────────────────────────────────────────────────────────────────
// Both forms declared at setup level — never inside a function.
// Calling useForm() inside a function loses the Inertia/axios CSRF context → 419.
const singleForm = useForm({
    first_name: '', last_name: '', middle_name: '',
    town_city: '', country: '', municipality: '', province: '', place_of_origin: '',
    sex: '', age: null, visitor_category: '', nationality: '',
    contact_number: '', remarks: '', purpose: '', purpose_other: '',
    duration_of_stay: '', is_day_tour: true, nights: null,
    destinations: [], profile_id: '', visit_id: '',
})
const groupForm = useForm({ members: [] })

const buildPayload = (m) => {
    // Nationality: per-member first, then shared, then derive from country
    const memberNat = m._nationalityManuallySet ? m.nationality : ''
    const resolvedNationality = memberNat || sharedNationality.value || ''

    // Country: per-member first, fall back to sharedCountry Foreign flag
    const resolvedCountry = m.country || (sharedCountry.value === 'Foreign' ? 'Foreign' : 'Philippines')

    // Middle name: trim and send empty string as null
    const resolvedMiddleName = (m.middle_name || '').trim() || null

    return {
        first_name:       m.first_name,
        last_name:        m.surname,
        middle_name:      resolvedMiddleName,
        town_city:        m.town_city,
        country:          resolvedCountry,
        municipality:     m.town_city,
        province:         resolvedCountry,
        place_of_origin:  `${m.town_city}, ${resolvedCountry}`,
        sex:              m.sex || sharedGender.value || '',
        age:              m.age ? parseInt(m.age) : null,
        visitor_category: m.visitor_category || sharedCategory.value || deriveCategory(m.age) || '',
        nationality:      resolvedNationality,
        contact_number:   m.contact_number || '',
        remarks:          m.remarks || '',
        purpose:          purpose.value,
        purpose_other:    purpose.value === 'Other' ? purposeOther.value : '',
        duration_of_stay: durationLabel.value || 'Day Tour',
        is_day_tour:      isDayTour.value,
        nights:           isDayTour.value ? null : (nights.value || null),
        destinations:     sharedAccommodations.value,
        profile_id:       m.profile_id || '',
        visit_id:         m.visit_id   || '',
    }
}

const submit = () => {
    const active = members.value.filter((m, i) => i === 0 || m.surname.trim() || m.first_name.trim())
    if (active.length === 1) {
        // Assign each field individually — Object.assign breaks Inertia's reactive form
        const payload = buildPayload(active[0])
        singleForm.first_name       = payload.first_name
        singleForm.last_name        = payload.last_name
        singleForm.middle_name      = payload.middle_name
        singleForm.town_city        = payload.town_city
        singleForm.country          = payload.country
        singleForm.municipality     = payload.municipality
        singleForm.province         = payload.province
        singleForm.place_of_origin  = payload.place_of_origin
        singleForm.sex              = payload.sex
        singleForm.age              = payload.age
        singleForm.visitor_category = payload.visitor_category
        singleForm.nationality      = payload.nationality
        singleForm.contact_number   = payload.contact_number
        singleForm.remarks          = payload.remarks
        singleForm.purpose          = payload.purpose
        singleForm.purpose_other    = payload.purpose_other
        singleForm.duration_of_stay = payload.duration_of_stay
        singleForm.is_day_tour      = payload.is_day_tour
        singleForm.nights           = payload.nights
        singleForm.destinations     = payload.destinations
        singleForm.profile_id       = payload.profile_id
        singleForm.visit_id         = payload.visit_id
        singleForm.post(route('registration.store'))
    } else {
        groupForm.members = active.map(buildPayload)
        groupForm.post(route('registration.group'))
    }
}

const purposeOptions = ['Tourism', 'Research', 'Event', 'Official Visit', 'Other']
</script>

<template>
    <LandingLayout>
        <!-- Top Bar -->
        <div class="container mx-auto px-2">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">
                <div class="flex-1 text-sm text-gray-500 font-medium">Registration</div>
                <div class="relative">
                    <button @click="showUser = !showUser">
                        <FontAwesomeIcon icon="user" class="text-gray-700 text-lg" />
                    </button>
                    <div v-if="showUser"
                        class="absolute right-0 mt-3 w-52 bg-white border border-gray-200 rounded-xl shadow-xl p-4 z-50 text-center">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ authUser?.name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6 px-4 max-w-5xl mx-auto">

            <!-- Step Indicator -->
            <div class="flex items-center justify-center mb-6">
                <div class="flex items-center gap-2">
                    <span class="bg-gray-900 text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">1</span>
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

            <!-- Pre-registration lookup -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-5">
                <p class="text-sm font-semibold text-gray-700 mb-3">
                    Pre-Registration Code
                    <span class="text-gray-400 font-normal ml-1">(optional — enter if visitor pre-registered online)</span>
                </p>
                <div v-if="preRegData"
                    class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-bold text-green-800">Pre-registration found — form pre-filled</span>
                        <span class="text-green-600 text-xs">· {{ preRegData.is_group ? `Group — ${preRegData.members?.length} member(s)` : 'Individual' }}</span>
                    </div>
                    <button @click="clearLookup" class="text-green-400 hover:text-red-500 text-xs font-bold ml-4">✕ Clear</button>
                </div>
                <div v-else class="flex gap-2">
                    <input v-model="refCode" type="text" placeholder="e.g. BEL-482951"
                        class="flex-1 border border-gray-200 rounded-xl py-2.5 px-4 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-gray-300"
                        @keyup.enter.prevent="lookupByCode" />
                    <button @click="lookupByCode" :disabled="lookupLoading || !refCode.trim()"
                        class="bg-gray-900 text-white text-sm font-bold px-5 py-2.5 rounded-xl disabled:opacity-50 hover:bg-gray-700 transition">
                        {{ lookupLoading ? 'Searching...' : 'Find' }}
                    </button>
                </div>
                <p v-if="lookupError" class="text-red-500 text-xs mt-2">{{ lookupError }}</p>
            </div>

            <!-- ══ TOURIST ARRIVAL FORM (mirrors physical form exactly) ══ -->
            <div class="bg-white border-2 border-gray-800 text-sm overflow-hidden rounded-sm shadow">

                <!-- ── Row 1: Official Header ── -->
                <!-- brgylogo: 1080x1080 square. dti_logo: 1536x1024 (3:2 ratio, wider than tall) -->
                <div class="border-b-2 border-gray-800">
                    <div style="display:flex;align-items:center;min-height:130px;">

                        <!-- Left: Municipality seal — 1080x1080 square, equal width/height -->
                        <div style="flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:6px 8px;">
                            <img src="/images/brgylogo.png" alt="Barangay Logo"
                                style="width:110px;height:110px;object-fit:contain;display:block;" />
                        </div>

                        <!-- Center: Title text -->
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:6px 4px;">
                            <p style="font-size:24px;font-weight:900;color:#111;letter-spacing:0.5px;margin:0 0 4px 0;line-height:1.1;">TOURIST ARRIVAL FORM</p>
                            <p style="font-size:12px;color:#555;margin:0;line-height:1.6;">Republic of the Philippines</p>
                            <p style="font-size:12px;color:#555;margin:0;line-height:1.6;">Province of Aklan</p>
                            <p style="font-size:14px;font-weight:700;color:#222;margin:0;line-height:1.6;">Municipality of Buruanga</p>
                            <p style="font-size:14px;font-weight:900;color:#111;letter-spacing:1px;margin:0;line-height:1.6;">MUNICIPAL TOURISM OFFICE</p>
                        </div>

                        <!-- Right: DTI logo — 1536x1024 = 3:2 ratio, so width = 1.5 × height -->
                        <!-- At height=90px → width=135px to preserve aspect ratio correctly -->
                        <div style="flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:6px 8px;">
                            <img src="/images/dti_logo.png" alt="DTI Logo"
                                style="height:90px;width:135px;object-fit:contain;display:block;" />
                        </div>

                    </div>
                </div>

                <!-- ── Row 2: No of Pax | Date | OR# — no inner column borders ── -->
                <div class="border-b-2 border-gray-800 flex">
                    <div class="flex-1 px-3 py-1.5 flex items-center gap-2">
                        <span class="font-bold text-xs text-gray-700 uppercase tracking-wide">NO OF PAX:</span>
                        <span class="font-bold text-gray-900">{{ activeMemberCount }}</span>
                    </div>
                    <div class="flex-1 px-3 py-1.5 flex items-center gap-2">
                        <span class="font-bold text-xs text-gray-700 uppercase tracking-wide">DATE:</span>
                        <span class="text-gray-800 text-xs">{{ new Date().toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' }) }}</span>
                    </div>
                    <div class="flex-1 px-3 py-1.5 flex items-center gap-2">
                        <span class="font-bold text-xs text-gray-700 uppercase tracking-wide">OR#:</span>
                        <span class="text-gray-400 text-xs italic">Generated at payment</span>
                    </div>
                </div>

                <!-- ── Row 3: Gender | Category | Nationality | Country ── -->
                <!-- No inner column borders — matches physical form -->
                <div class="border-b-2 border-gray-800 flex">

                    <!-- Gender -->
                    <div class="w-28 shrink-0 px-3 py-2">
                        <p class="font-bold text-xs text-gray-800 uppercase mb-2">GENDER</p>
                        <label class="flex items-center gap-2 mb-1.5 cursor-pointer">
                            <input type="radio" name="form-shared-gender" value="M" v-model="sharedGender"
                                class="text-gray-900 focus:ring-0" />
                            <span class="text-xs text-gray-700">MALE</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="form-shared-gender" value="F" v-model="sharedGender"
                                class="text-gray-900 focus:ring-0" />
                            <span class="text-xs text-gray-700">FEMALE</span>
                        </label>
                    </div>

                    <!-- Category -->
                    <div class="flex-1 px-3 py-2">
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5">
                            <label v-for="cat in feeCategories" :key="cat.id"
                                class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="form-shared-category" :value="cat.category"
                                    v-model="sharedCategory" class="text-gray-900 focus:ring-0" />
                                <span class="text-xs text-gray-700 uppercase">
                                    {{ cat.category }}
                                    <span v-if="cat.age_range" class="text-gray-400">({{ cat.age_range }})</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Nationality -->
                    <div class="w-36 shrink-0 px-3 py-2">
                        <p class="font-bold text-xs text-gray-800 uppercase mb-2">NATIONALITY</p>
                        <label v-for="nat in ['Local','Aklanon','OFW']" :key="nat"
                            class="flex items-center gap-2 mb-1.5 cursor-pointer">
                            <input type="radio" name="form-shared-nationality" :value="nat"
                                v-model="sharedNationality" class="text-gray-900 focus:ring-0" />
                            <span class="text-xs text-gray-700 uppercase">{{ nat }}</span>
                        </label>
                        <!-- OFW text field -->
                        <div v-if="sharedNationality === 'OFW'" class="mt-1">
                            <input type="text" placeholder="Country..."
                                class="w-full border-b border-gray-400 bg-transparent text-xs focus:outline-none px-1 py-0.5" />
                        </div>
                    </div>

                    <!-- Country (Foreign) -->
                    <div class="w-28 shrink-0 px-3 py-2">
                        <p class="font-bold text-xs text-gray-800 uppercase mb-2">COUNTRY</p>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="sharedCountry" true-value="Foreign" false-value=""
                                class="rounded text-gray-900 focus:ring-0" />
                            <span class="text-xs text-gray-700">FOREIGN</span>
                        </label>
                    </div>
                </div>

                <!-- ── Row 4: Accommodation | Duration of Stay ── -->
                <!-- No inner column borders — matches physical form -->
                <div class="border-b-2 border-gray-800 flex">

                    <!-- Accommodation -->
                    <div class="flex-1 px-3 py-2">
                        <p class="font-bold text-xs text-gray-800 uppercase tracking-wide mb-1">
                            ACCOMMODATION:
                            <span class="font-normal normal-case text-gray-500 ml-1">Resort / Cottage Name</span>
                        </p>
                        <DestinationChecklist v-model="sharedAccommodations" :attractions="barangayAttractions" />
                    </div>

                    <!-- Duration -->
                    <div class="w-72 shrink-0 px-3 py-2">
                        <p class="font-bold text-xs text-gray-800 uppercase tracking-wide mb-2">DURATION OF STAY</p>

                        <!-- No. of Nights -->
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs text-gray-700 w-24">No. of NIGHTS</span>
                            <input v-model="nights" type="number" min="1"
                                :disabled="isDayTour"
                                :placeholder="isDayTour ? '—' : '0'"
                                class="w-16 border-b border-gray-400 bg-transparent text-xs text-center focus:outline-none px-1 py-0.5 disabled:text-gray-300" />
                        </div>

                        <!-- Day Tour Only toggle -->
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-gray-700">✓ DAY TOUR Only:</span>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="form-day-tour" :value="true" v-model="isDayTour"
                                    class="text-gray-900 focus:ring-0" />
                                <span class="text-xs text-gray-700">YES</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="form-day-tour" :value="false" v-model="isDayTour"
                                    class="text-gray-900 focus:ring-0" />
                                <span class="text-xs text-gray-700">NO</span>
                            </label>
                        </div>

                        <!-- Purpose of Visit — placed here like the form note area -->
                        <div class="mt-3 border-t border-gray-200 pt-2">
                            <p class="font-bold text-xs text-gray-700 mb-1.5">PURPOSE OF VISIT:</p>
                            <div class="grid grid-cols-2 gap-x-2 gap-y-1">
                                <label v-for="opt in purposeOptions" :key="opt"
                                    class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="form-shared-purpose" :value="opt" v-model="purpose"
                                        class="text-gray-900 focus:ring-0" />
                                    <span class="text-xs text-gray-700">{{ opt }}</span>
                                </label>
                            </div>
                            <input v-if="purpose === 'Other'" v-model="purposeOther"
                                placeholder="Please specify..."
                                class="mt-1.5 w-full border-b border-gray-400 bg-transparent text-xs focus:outline-none px-1 py-0.5" />
                        </div>
                    </div>
                </div>

                <!-- ── Row 5: Visitor Table — exactly 6 rows like physical form ── -->
                <div class="overflow-x-auto border-t-2 border-gray-800">
                    <table class="w-full min-w-[820px] border-collapse text-xs">
                        <thead>
                            <tr class="border-b-2 border-gray-800">
                                <th class="border-r-2 border-gray-800 px-2 py-2 w-10 text-center font-bold text-gray-800 bg-gray-50">No.</th>
                                <th class="border-r-2 border-gray-800 px-2 py-2 text-center font-bold text-gray-800 bg-gray-50 min-w-[200px]">
                                    NAME
                                    <div class="font-normal text-gray-500">(Surname, First Name, Middle)</div>
                                </th>
                                <th class="border-r-2 border-gray-800 px-2 py-2 text-center font-bold text-gray-800 bg-gray-50 min-w-[150px]">
                                    ADDRESS
                                    <div class="font-normal text-gray-500">(Town/City and Country)</div>
                                </th>
                                <th class="border-r-2 border-gray-800 px-2 py-2 text-center font-bold text-gray-800 bg-gray-50 w-14">Sex</th>
                                <th class="border-r-2 border-gray-800 px-2 py-2 text-center font-bold text-gray-800 bg-gray-50 w-20">Age</th>
                                <th class="border-r-2 border-gray-800 px-2 py-2 text-center font-bold text-gray-800 bg-gray-50 w-32">Contact Number</th>
                                <th class="px-2 py-2 text-center font-bold text-gray-800 bg-gray-50 min-w-[130px]">
                                    Remarks
                                    <div class="font-normal text-gray-500">(Complain/Concerns &amp; Suggestions)</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows 1-6: always render exactly 6 rows -->
                            <template v-for="rowIndex in 6" :key="rowIndex">
                                <!-- If a member exists for this row index, render filled row -->
                                <tr v-if="members[rowIndex - 1]"
                                    class="border-b-2 border-gray-800 hover:bg-gray-50/50 transition"
                                    :class="rowIndex === 1 ? 'bg-blue-50/20' : ''">

                                    <!-- Row number -->
                                    <td class="border-r-2 border-gray-800 px-2 py-3 text-center align-middle font-bold text-gray-700 w-10">
                                        {{ rowIndex }}
                                        <div v-if="rowIndex === 1" class="text-gray-400 font-normal">✓</div>
                                    </td>

                                    <!-- Name cell -->
                                    <td class="border-r-2 border-gray-800 px-2 py-2 align-top min-w-[200px]">
                                        <!-- Row 1: show representative badge with gender/category from top section -->
                                        <div v-if="rowIndex === 1" class="mb-1.5 flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-bold bg-gray-800 text-white px-1.5 py-0.5 rounded">Representative</span>
                                            <span v-if="sharedGender" class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                                                {{ sharedGender === 'M' ? 'Male' : 'Female' }}
                                            </span>
                                            <span v-if="sharedCategory" class="text-xs bg-blue-50 border border-blue-200 text-blue-700 px-1.5 py-0.5 rounded-full font-semibold">
                                                {{ sharedCategory }}
                                            </span>
                                        </div>
                                        <!-- Returning visitor search (row 1 only for simplicity, or all rows) -->
                                        <div v-if="!members[rowIndex-1].visit_id" class="mb-1 relative">
                                            <div v-if="members[rowIndex-1].search.selected"
                                                class="flex items-center gap-1 bg-blue-50 border border-blue-200 rounded px-2 py-1 text-xs mb-1">
                                                <span class="text-blue-700 font-semibold truncate">{{ members[rowIndex-1].search.selected.full_name }}</span>
                                                <button @click="clearProfile(members[rowIndex-1])" class="ml-auto text-blue-400 hover:text-red-500 shrink-0">✕</button>
                                            </div>
                                            <input v-else v-model="members[rowIndex-1].search.query"
                                                @input="runSearch(members[rowIndex-1])"
                                                type="text" placeholder="Search returning visitor..."
                                                class="w-full text-xs text-gray-500 border-b border-dashed border-gray-300 bg-transparent focus:outline-none focus:border-blue-400 py-0.5 mb-1" />
                                            <ul v-if="members[rowIndex-1].search.results.length"
                                                class="absolute z-30 w-full mt-0.5 bg-white border border-gray-200 rounded shadow-lg max-h-32 overflow-auto">
                                                <li v-for="p in members[rowIndex-1].search.results" :key="p.id"
                                                    @click="selectProfile(members[rowIndex-1], p)"
                                                    class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-xs border-b last:border-0">
                                                    <span class="font-semibold text-gray-800">{{ p.full_name }}</span>
                                                    <span class="text-gray-400 ml-2">{{ p.visit_count }} visit(s)</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <span v-if="members[rowIndex-1].reference_code"
                                            class="inline-block font-mono text-xs bg-green-50 text-green-700 border border-green-200 px-1.5 py-0.5 rounded mb-1">
                                            {{ members[rowIndex-1].reference_code }}
                                        </span>
                                        <input v-model="members[rowIndex-1].surname"
                                            :disabled="!!members[rowIndex-1].search.selected"
                                            placeholder="Surname *"
                                            class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5 mb-1"
                                            :class="members[rowIndex-1].search.selected ? 'text-gray-400' : 'text-gray-800'" />
                                        <input v-model="members[rowIndex-1].first_name"
                                            :disabled="!!members[rowIndex-1].search.selected"
                                            placeholder="First Name *"
                                            class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5 mb-1"
                                            :class="members[rowIndex-1].search.selected ? 'text-gray-400' : 'text-gray-800'" />
                                        <input v-model="members[rowIndex-1].middle_name"
                                            placeholder="Middle Name"
                                            class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5 text-gray-700" />
                                    </td>

                                    <!-- Address cell -->
                                    <td class="border-r-2 border-gray-800 px-2 py-2 align-top min-w-[150px]">
                                        <div class="relative mb-1">
                                            <input v-model="members[rowIndex-1].town_city"
                                                @focus="members[rowIndex-1].showTownSug = true"
                                                @blur="setTimeout(() => members[rowIndex-1].showTownSug = false, 150)"
                                                placeholder="Town / City *"
                                                class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5" />
                                            <ul v-if="members[rowIndex-1].showTownSug && allAddresses.filter(a => a !== members[rowIndex-1].town_city && a.toLowerCase().includes((members[rowIndex-1].town_city||'').toLowerCase())).length"
                                                class="absolute z-30 w-full mt-0.5 bg-white border border-gray-200 rounded shadow-lg max-h-24 overflow-auto">
                                                <li v-for="addr in allAddresses.filter(a => a !== members[rowIndex-1].town_city && a.toLowerCase().includes((members[rowIndex-1].town_city||'').toLowerCase()))"
                                                    :key="addr"
                                                    @mousedown.prevent="members[rowIndex-1].town_city = addr; members[rowIndex-1].showTownSug = false"
                                                    class="px-2 py-1.5 hover:bg-gray-50 cursor-pointer text-xs">{{ addr }}</li>
                                            </ul>
                                        </div>
                                        <div class="relative">
                                            <input v-model="members[rowIndex-1].country"
                                                @focus="members[rowIndex-1].showCountrySug = true"
                                                @blur="setTimeout(() => members[rowIndex-1].showCountrySug = false, 150)"
                                                placeholder="Country (blank = PH)"
                                                class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5" />
                                            <ul v-if="members[rowIndex-1].showCountrySug && allAddresses.filter(a => a !== members[rowIndex-1].country && a.toLowerCase().includes((members[rowIndex-1].country||'').toLowerCase())).length"
                                                class="absolute z-30 w-full mt-0.5 bg-white border border-gray-200 rounded shadow-lg max-h-24 overflow-auto">
                                                <li v-for="addr in allAddresses.filter(a => a !== members[rowIndex-1].country && a.toLowerCase().includes((members[rowIndex-1].country||'').toLowerCase()))"
                                                    :key="addr"
                                                    @mousedown.prevent="members[rowIndex-1].country = addr; members[rowIndex-1].showCountrySug = false"
                                                    class="px-2 py-1.5 hover:bg-gray-50 cursor-pointer text-xs">{{ addr }}</li>
                                            </ul>
                                        </div>
                                    </td>

                                    <!-- Sex -->
                                    <td class="border-r-2 border-gray-800 px-2 py-2 text-center align-middle w-14">
                                        <div class="flex flex-col gap-2 items-center">
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="radio" :name="`sex-${rowIndex}`" value="M"
                                                    v-model="members[rowIndex-1].sex"
                                                    class="text-gray-900 focus:ring-0" />
                                                <span class="text-xs">M</span>
                                            </label>
                                            <label class="flex items-center gap-1 cursor-pointer">
                                                <input type="radio" :name="`sex-${rowIndex}`" value="F"
                                                    v-model="members[rowIndex-1].sex"
                                                    class="text-gray-900 focus:ring-0" />
                                                <span class="text-xs">F</span>
                                            </label>
                                        </div>
                                    </td>

                                    <!-- Age -->
                                    <td class="border-r-2 border-gray-800 px-2 py-2 text-center align-middle w-20">
                                        <input v-model="members[rowIndex-1].age"
                                            type="number" min="0" max="120" placeholder="—"
                                            style="color:#111 !important;-moz-appearance:textfield;font-size:13px;font-weight:600;"
                                            class="w-full border border-gray-300 rounded bg-white text-center focus:outline-none focus:border-gray-600 py-1 px-1 mb-1 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                        <span v-if="members[rowIndex-1].visitor_category"
                                            class="inline-block text-xs bg-blue-50 border border-blue-200 text-blue-700 font-semibold px-1 py-0.5 rounded-full leading-tight">
                                            {{ members[rowIndex-1].visitor_category }}
                                        </span>
                                    </td>

                                    <!-- Contact -->
                                    <td class="border-r-2 border-gray-800 px-2 py-2 align-middle w-32">
                                        <input v-model="members[rowIndex-1].contact_number"
                                            type="tel" placeholder="09xxxxxxxxx"
                                            class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5" />
                                    </td>

                                    <!-- Remarks -->
                                    <td class="px-2 py-2 align-middle">
                                        <div class="flex items-center gap-1">
                                            <input v-model="members[rowIndex-1].remarks"
                                                placeholder="Remarks"
                                                class="flex-1 border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5" />
                                            <!-- Remove button for rows 2-6 -->
                                            <button v-if="rowIndex > 1 && !preRegData"
                                                @click="removeRow(rowIndex - 1)"
                                                class="text-red-400 hover:text-red-600 font-bold text-xs shrink-0 ml-1">✕</button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Empty row — transparent borders, matches physical form blank rows -->
                                <tr v-else class="border-b border-gray-200 h-12 cursor-pointer hover:bg-gray-50/40 transition"
                                    @click="rowIndex <= members.length + 1 && !preRegData ? addRow() : null">
                                    <td class="border-r border-gray-200 px-2 text-center align-middle font-bold text-gray-400">
                                        {{ rowIndex }}
                                    </td>
                                    <td class="border-r border-gray-200 px-2 text-center align-middle">
                                        <span v-if="rowIndex === members.length + 1 && !preRegData"
                                            class="text-xs text-gray-300 italic">+ click to add member</span>
                                    </td>
                                    <td class="border-r border-gray-200"></td>
                                    <td class="border-r border-gray-200"></td>
                                    <td class="border-r border-gray-200"></td>
                                    <td class="border-r border-gray-200"></td>
                                    <td></td>
                                </tr>
                            </template>

                            <!-- Extra rows if members exceed 6 -->
                            <tr v-for="(m, extraIdx) in members.slice(6)" :key="`extra-${extraIdx}`"
                                class="border-b-2 border-gray-800 hover:bg-gray-50/50 transition">
                                <td class="border-r-2 border-gray-800 px-2 py-2 text-center align-middle font-bold text-gray-700">
                                    {{ extraIdx + 7 }}
                                </td>
                                <td class="border-r-2 border-gray-800 px-2 py-2 align-top">
                                    <input v-model="m.surname" placeholder="Surname *"
                                        class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none py-0.5 mb-1" />
                                    <input v-model="m.first_name" placeholder="First Name *"
                                        class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none py-0.5 mb-1" />
                                    <input v-model="m.middle_name" placeholder="Middle Name"
                                        class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none py-0.5" />
                                </td>
                                <td class="border-r-2 border-gray-800 px-2 py-2 align-top">
                                    <input v-model="m.town_city" placeholder="Town / City *"
                                        class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none py-0.5 mb-1" />
                                    <input v-model="m.country" placeholder="Country"
                                        class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none py-0.5" />
                                </td>
                                <td class="border-r-2 border-gray-800 px-2 py-2 text-center align-middle">
                                    <div class="flex flex-col gap-1.5 items-center">
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input type="radio" :name="`sex-extra-${extraIdx}`" value="M" v-model="m.sex" class="focus:ring-0" />
                                            <span class="text-xs">M</span>
                                        </label>
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input type="radio" :name="`sex-extra-${extraIdx}`" value="F" v-model="m.sex" class="focus:ring-0" />
                                            <span class="text-xs">F</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="border-r-2 border-gray-800 px-2 py-2 text-center align-middle">
                                    <input v-model="m.age" type="number" min="0" max="120" placeholder="—"
                                        style="color:#111;-moz-appearance:textfield;"
                                        class="w-full border-b border-gray-300 bg-transparent text-xs text-center focus:outline-none py-0.5 mb-1 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                    <span v-if="m.visitor_category"
                                        class="inline-block text-xs bg-blue-50 border border-blue-200 text-blue-700 font-semibold px-1 py-0.5 rounded-full">
                                        {{ m.visitor_category }}
                                    </span>
                                </td>
                                <td class="border-r-2 border-gray-800 px-2 py-2 align-middle">
                                    <input v-model="m.contact_number" type="tel" placeholder="09xxxxxxxxx"
                                        class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none py-0.5" />
                                </td>
                                <td class="px-2 py-2 align-middle">
                                    <div class="flex items-center gap-1">
                                        <input v-model="m.remarks" placeholder="Remarks"
                                            class="flex-1 border-b border-gray-300 bg-transparent text-xs focus:outline-none py-0.5" />
                                        <button @click="removeRow(extraIdx + 6)"
                                            class="text-red-400 hover:text-red-600 font-bold text-xs shrink-0 ml-1">✕</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ── Footer: Signatures (inside the form border, no divider between) ── -->
                <div class="border-t-2 border-gray-800 flex">
                    <!-- Left: Guest Signature + note -->
                    <div class="flex-1 px-4 py-3 space-y-2">
                        <div class="flex items-end gap-3">
                            <span class="text-xs font-bold text-gray-800">GUEST SIGNATURE:</span>
                            <div class="w-40 border-b border-gray-700 mb-0.5"></div>
                        </div>
                        <p class="text-xs text-red-600 italic">
                            NOTE: Buruanganon guest required to fill out this form for monitoring of Statistics only. Thank you
                        </p>
                    </div>
                    <!-- Right: Clerk Signature + note (no left border — matches physical form) -->
                    <div class="flex-1 px-4 py-3 space-y-2">
                        <div class="flex items-end gap-3">
                            <span class="text-xs font-bold text-gray-800">TOURISM CLERK SIGNATURE:</span>
                            <div class="w-28 border-b border-gray-700 mb-0.5"></div>
                        </div>
                        <p class="text-xs text-red-600 italic">
                            NOTE: Please Fill Out the required fields in this form to complete your registration. Thank you
                        </p>
                    </div>
                </div>

            </div>
            <!-- ── Register button — OUTSIDE the form ── -->
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span v-if="isGroup" class="text-sm font-bold bg-gray-800 text-white px-3 py-1.5 rounded-full">
                        Group · {{ activeMemberCount }} person(s)
                    </span>
                </div>
                <div v-if="can('edit_registration')">
                    <button @click="submit" :disabled="groupForm.processing"
                        class="bg-gray-900 text-white font-bold py-3 px-10 rounded-xl disabled:opacity-50 text-sm hover:bg-black transition shadow-lg">
                        {{ groupForm.processing
                            ? 'Registering...'
                            : (isGroup ? `Register ${activeMemberCount} Visitor(s) →` : 'Register Visitor →') }}
                    </button>
                </div>
                <p v-else class="text-xs text-red-500">You don't have permission to register visitors.</p>
            </div>
        </div>
    </LandingLayout>
</template>