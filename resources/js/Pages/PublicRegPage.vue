<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/LandingLayout.vue'
import DestinationChecklist from '@/Components/DestinationChecklist.vue'

const props = defineProps({
    feeCategories:       { type: Array, default: () => [] },
    barangayAttractions: { type: Array, default: () => [] },
    formFields:          { type: Array, default: () => [] },
})

// ── Flash (success screen) ────────────────────────────────────────────────────
const page          = usePage()
const flash         = computed(() => page.props.flash ?? {})
const submitted     = computed(() => flash.value.success === true || flash.value.success === 'true')
const flashMode     = computed(() => flash.value.mode ?? 'single')
const referenceCode = computed(() => flash.value.reference_code ?? '')
const fullName      = computed(() => flash.value.full_name ?? '')
const groupMembers  = computed(() => flash.value.members ?? [])
const groupCode     = computed(() => flash.value.group_code ?? '')

const qrUrl = (code) =>
    code ? `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(code)}` : ''

// ── Form field settings ───────────────────────────────────────────────────────
const isVisible = (key) => props.formFields.find(f => f.field_key === key)?.is_visible ?? true

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
    if (c === 'philippines' || c === 'ph' || c === '') return 'Local'
    if (c.includes('aklan') || c.includes('buruanga')) return 'Aklanon'
    return 'Foreign'
}

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
    showTownSug:      false,
    showCountrySug:   false,
    _nationalityManuallySet: false,
})

// ── Members ───────────────────────────────────────────────────────────────────
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

// ── Address autocomplete ──────────────────────────────────────────────────────
const allAddresses = computed(() => {
    const addrs = new Set()
    members.value.forEach(m => {
        if (m.town_city) addrs.add(m.town_city)
        if (m.country)   addrs.add(m.country)
    })
    return [...addrs]
})

// ── Age → category watcher ────────────────────────────────────────────────────
const setupAgeWatch = (i) => {
    watch(() => members.value[i]?.age, (age) => {
        if (members.value[i]) members.value[i].visitor_category = deriveCategory(age)
    })
}
members.value.forEach((_, i) => setupAgeWatch(i))
watch(() => members.value.length, (len) => setupAgeWatch(len - 1))

// ── Country → nationality watcher ─────────────────────────────────────────────
watch(() => members.value.map(m => m.country), (countries) => {
    countries.forEach((c, i) => {
        if (members.value[i] && !members.value[i]._nationalityManuallySet) {
            members.value[i].nationality = deriveNationality(c)
        }
    })
}, { deep: true })

// ── Shared fields ─────────────────────────────────────────────────────────────
const sharedGender      = ref('')
const sharedCategory    = ref('')
const sharedNationality = ref('')
const sharedCountry     = ref('')
const sharedAccommodations = ref([])
const isDayTour         = ref(true)
const nights            = ref('')
const purpose           = ref('')
const purposeOther      = ref('')

const durationLabel = computed(() => {
    if (isDayTour.value) return 'Day Tour'
    return nights.value ? `${nights.value} night(s)` : ''
})

// ── Submit ────────────────────────────────────────────────────────────────────
// Both forms at setup level — useForm() inside a function loses CSRF context → 419
const singleForm = useForm({
    first_name: '', last_name: '', middle_name: '',
    town_city: '', country: '', municipality: '', province: '', place_of_origin: '',
    sex: '', age: null, visitor_category: '', nationality: '',
    contact_number: '', remarks: '', purpose: '', purpose_other: '',
    duration_of_stay: '', is_day_tour: true, nights: null, destinations: [],
})
const groupForm = useForm({ members: [] })

const buildPayload = (m) => {
    const memberNat       = m._nationalityManuallySet ? m.nationality : ''
    const resolvedNat     = memberNat || sharedNationality.value || ''
    const resolvedCountry = m.country || (sharedCountry.value === 'Foreign' ? 'Foreign' : 'Philippines')
    const resolvedMiddle  = (m.middle_name || '').trim() || null

    return {
        first_name:       m.first_name,
        last_name:        m.surname,
        middle_name:      resolvedMiddle,
        town_city:        m.town_city,
        country:          resolvedCountry,
        municipality:     m.town_city,
        province:         resolvedCountry,
        place_of_origin:  `${m.town_city}, ${resolvedCountry}`,
        sex:              m.sex || sharedGender.value || '',
        age:              m.age ? parseInt(m.age) : null,
        visitor_category: m.visitor_category || sharedCategory.value || deriveCategory(m.age) || '',
        nationality:      resolvedNat,
        contact_number:   m.contact_number || '',
        remarks:          m.remarks || '',
        purpose:          purpose.value,
        purpose_other:    purpose.value === 'Other' ? purposeOther.value : '',
        duration_of_stay: durationLabel.value || 'Day Tour',
        is_day_tour:      isDayTour.value,
        nights:           isDayTour.value ? null : (nights.value || null),
        destinations:     sharedAccommodations.value,
    }
}

const submit = () => {
    const active = members.value.filter((m, i) => i === 0 || m.surname.trim() || m.first_name.trim())
    if (active.length === 1) {
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
        singleForm.post(route('pre-register.store'), {
            preserveScroll: true,
            onSuccess: () => window.scrollTo({ top: 0, behavior: 'smooth' })
        })
    } else {
        groupForm.members = active.map(buildPayload)
        groupForm.post(route('pre-register.group'), {
            preserveScroll: true,
            onSuccess: () => window.scrollTo({ top: 0, behavior: 'smooth' })
        })
    }
}

const purposeOptions = ['Tourism', 'Research', 'Event', 'Official Visit', 'Other']
</script>

<template>
    <LandingLayout>
        <div class="min-h-screen bg-gray-50">

            <!-- ══ SUCCESS SCREEN ══ -->
            <div v-if="submitted" class="px-4 py-16">
                <div class="flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 max-w-md w-full text-center">
                        <div class="flex justify-center mb-4">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z"/>
                                </svg>
                            </div>
                        </div>

                        <template v-if="flashMode === 'single'">
                            <h1 class="text-2xl font-bold text-gray-800 mb-1">You're Pre-Registered!</h1>
                            <p class="text-gray-500 text-sm mb-6">
                                Welcome, <span class="font-semibold text-gray-700">{{ fullName }}</span>.
                                Show your reference code at the checkpoint.
                            </p>
                            <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl px-6 py-5 mb-6">
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Your Reference Code</p>
                                <p class="text-4xl font-mono font-bold text-gray-900 tracking-widest">{{ referenceCode }}</p>
                            </div>
                            <div class="flex flex-col items-center mb-6">
                                <p class="text-xs text-gray-400 mb-3">Or let staff scan this QR code</p>
                                <img :src="qrUrl(referenceCode)" :alt="referenceCode"
                                    class="w-44 h-44 border border-gray-200 rounded-xl p-2" />
                            </div>
                        </template>

                        <template v-else>
                            <h1 class="text-2xl font-bold text-gray-800 mb-1">Your Group is Pre-Registered!</h1>
                            <p class="text-gray-500 text-sm mb-6">{{ groupMembers.length }} member(s) registered.</p>
                            <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl px-6 py-5 mb-6">
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Group Reference Code</p>
                                <p class="text-4xl font-mono font-bold text-gray-900 tracking-widest">{{ groupCode }}</p>
                            </div>
                            <div class="flex flex-col items-center mb-6">
                                <p class="text-xs text-gray-400 mb-3">Or let staff scan this QR code</p>
                                <img :src="qrUrl(groupCode)" :alt="groupCode"
                                    class="w-44 h-44 border border-gray-200 rounded-xl p-2" />
                            </div>
                        </template>

                        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-6 text-left">
                            <p class="text-sm font-semibold text-amber-800">📸 Screenshot this screen</p>
                            <p class="text-xs text-amber-700 mt-1">Show your reference code or QR at the Bel-is Tourism Hub checkpoint to complete your entry.</p>
                        </div>
                        <Link :href="route('home')"
                            class="block w-full bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-gray-700 transition text-sm text-center">
                            Back to Bel-is Website
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ══ REGISTRATION FORM ══ -->
            <div v-else class="pt-28 pb-12 px-4">

                <!-- Page header -->
                <div class="max-w-5xl mx-auto text-center mb-5">
                    <h1 class="text-2xl font-bold text-gray-800">Visitor Pre-Registration</h1>
                    <p class="text-gray-500 text-sm mt-1">Fill out this form before arriving at Barangay Bel-is. You will receive a reference code to show at the checkpoint.</p>
                </div>

                <!-- Privacy Notice -->
                <div class="max-w-5xl mx-auto mb-5">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 flex items-start gap-3">
                        <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-xs text-blue-700"><span class="font-semibold">Privacy Notice (RA 10173):</span> Your personal information is collected solely for tourism management and environmental fee recording by Barangay Bel-is, Buruanga, Aklan.</p>
                    </div>
                </div>

                <!-- ══ TOURIST ARRIVAL FORM ══ -->
                <div class="max-w-5xl mx-auto">
                    <div class="bg-white border-2 border-gray-800 text-sm overflow-hidden rounded-sm shadow">

                        <!-- ── Row 1: Official Header ── -->
                        <div class="border-b-2 border-gray-800">
                            <div style="display:flex;align-items:stretch;min-height:130px;">
                                <!-- Left: Municipality seal -->
                                <div style="flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:6px 10px;">
                                    <img src="/images/brgylogo.png" alt="Barangay Logo"
                                        style="width:110px;height:110px;object-fit:contain;display:block;" />
                                </div>
                                <!-- Center: Title -->
                                <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:6px 8px;">
                                    <p style="font-size:24px;font-weight:900;color:#111;letter-spacing:0.5px;margin:0 0 4px 0;line-height:1.1;">TOURIST ARRIVAL FORM</p>
                                    <p style="font-size:12px;color:#555;margin:0;line-height:1.6;">Republic of the Philippines</p>
                                    <p style="font-size:12px;color:#555;margin:0;line-height:1.6;">Province of Aklan</p>
                                    <p style="font-size:14px;font-weight:700;color:#222;margin:0;line-height:1.6;">Municipality of Buruanga</p>
                                    <p style="font-size:14px;font-weight:900;color:#111;letter-spacing:1px;margin:0;line-height:1.6;">MUNICIPAL TOURISM OFFICE</p>
                                </div>
                                <!-- Right: DTI seal — 1536x1024 = 3:2 ratio -->
                                <div style="flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:6px 8px;">
                                    <img src="/images/dti_logo.png" alt="DTI Logo"
                                        style="height:90px;width:135px;object-fit:contain;display:block;" />
                                </div>
                            </div>
                        </div>

                        <!-- ── Row 2: No of Pax | Date | OR# ── -->
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
                        <div class="border-b-2 border-gray-800 flex">
                            <!-- Gender -->
                            <div class="w-28 shrink-0 px-3 py-2">
                                <p class="font-bold text-xs text-gray-800 uppercase mb-2">GENDER</p>
                                <label class="flex items-center gap-2 mb-1.5 cursor-pointer">
                                    <input type="radio" name="pubGender" value="M" v-model="sharedGender" class="text-gray-900 focus:ring-0" />
                                    <span class="text-xs text-gray-700">MALE</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="pubGender" value="F" v-model="sharedGender" class="text-gray-900 focus:ring-0" />
                                    <span class="text-xs text-gray-700">FEMALE</span>
                                </label>
                            </div>
                            <!-- Category -->
                            <div class="flex-1 px-3 py-2">
                                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5">
                                    <label v-for="cat in feeCategories" :key="cat.id"
                                        class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="pubCategory" :value="cat.category"
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
                                    <input type="radio" name="pubNationality" :value="nat"
                                        v-model="sharedNationality" class="text-gray-900 focus:ring-0" />
                                    <span class="text-xs text-gray-700 uppercase">{{ nat }}</span>
                                </label>
                            </div>
                            <!-- Country -->
                            <div class="w-28 shrink-0 px-3 py-2">
                                <p class="font-bold text-xs text-gray-800 uppercase mb-2">COUNTRY</p>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="sharedCountry" true-value="Foreign" false-value=""
                                        class="rounded text-gray-900 focus:ring-0" />
                                    <span class="text-xs text-gray-700">FOREIGN</span>
                                </label>
                            </div>
                        </div>

                        <!-- ── Row 4: Accommodation | Duration ── -->
                        <div class="border-b-2 border-gray-800 flex">
                            <!-- Accommodation -->
                            <div class="flex-1 px-3 py-2">
                                <p class="font-bold text-xs text-gray-800 uppercase tracking-wide mb-1">
                                    ACCOMMODATION:
                                    <span class="font-normal normal-case text-gray-500 ml-1">Resort / Cottage Name</span>
                                </p>
                                <DestinationChecklist v-model="sharedAccommodations" :attractions="barangayAttractions" />
                            </div>
                            <!-- Duration + Purpose -->
                            <div class="w-72 shrink-0 px-3 py-2">
                                <p class="font-bold text-xs text-gray-800 uppercase tracking-wide mb-2">DURATION OF STAY</p>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs text-gray-700 w-24">No. of NIGHTS</span>
                                    <input v-model="nights" type="number" min="1"
                                        :disabled="isDayTour"
                                        :placeholder="isDayTour ? '—' : '0'"
                                        class="w-16 border-b border-gray-400 bg-transparent text-xs text-center focus:outline-none px-1 py-0.5 disabled:text-gray-300" />
                                </div>
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-xs font-bold text-gray-700">✓ DAY TOUR Only:</span>
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="pubDayTour" :value="true" v-model="isDayTour" class="text-gray-900 focus:ring-0" />
                                        <span class="text-xs text-gray-700">YES</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="pubDayTour" :value="false" v-model="isDayTour" class="text-gray-900 focus:ring-0" />
                                        <span class="text-xs text-gray-700">NO</span>
                                    </label>
                                </div>
                                <!-- Purpose -->
                                <div class="border-t border-gray-200 pt-2">
                                    <p class="font-bold text-xs text-gray-700 mb-1.5">PURPOSE OF VISIT:</p>
                                    <div class="grid grid-cols-2 gap-x-2 gap-y-1">
                                        <label v-for="opt in purposeOptions" :key="opt"
                                            class="flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" name="pubPurpose" :value="opt" v-model="purpose"
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

                        <!-- ── Visitor Table — exactly 6 rows ── -->
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
                                    <template v-for="rowIndex in 6" :key="rowIndex">
                                        <!-- Filled row -->
                                        <tr v-if="members[rowIndex - 1]"
                                            class="border-b-2 border-gray-800 hover:bg-gray-50/50 transition"
                                            :class="rowIndex === 1 ? 'bg-blue-50/20' : ''">
                                            <!-- No. -->
                                            <td class="border-r-2 border-gray-800 px-2 py-3 text-center align-middle font-bold text-gray-700 w-10">
                                                {{ rowIndex }}
                                                <div v-if="rowIndex === 1" class="text-gray-400 font-normal text-xs">✓</div>
                                            </td>
                                            <!-- Name -->
                                            <td class="border-r-2 border-gray-800 px-2 py-2 align-top min-w-[200px]">
                                                <input v-model="members[rowIndex-1].surname" placeholder="Surname *"
                                                    class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5 mb-1 text-gray-800" />
                                                <input v-model="members[rowIndex-1].first_name" placeholder="First Name *"
                                                    class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5 mb-1 text-gray-800" />
                                                <input v-model="members[rowIndex-1].middle_name" placeholder="Middle Name"
                                                    class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5 text-gray-700" />
                                            </td>
                                            <!-- Address -->
                                            <td class="border-r-2 border-gray-800 px-2 py-2 align-top min-w-[150px]">
                                                <div class="relative mb-1">
                                                    <input v-model="members[rowIndex-1].town_city"
                                                        @focus="members[rowIndex-1].showTownSug = true"
                                                        @blur="setTimeout(() => members[rowIndex-1].showTownSug = false, 150)"
                                                        placeholder="Town / City *"
                                                        class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5 text-gray-800" />
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
                                                        class="w-full border-b border-gray-300 bg-transparent text-xs focus:outline-none focus:border-gray-600 py-0.5 text-gray-800" />
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
                                                        <input type="radio" :name="`pub-sex-${rowIndex}`" value="M"
                                                            v-model="members[rowIndex-1].sex" class="focus:ring-0" />
                                                        <span class="text-xs">M</span>
                                                    </label>
                                                    <label class="flex items-center gap-1 cursor-pointer">
                                                        <input type="radio" :name="`pub-sex-${rowIndex}`" value="F"
                                                            v-model="members[rowIndex-1].sex" class="focus:ring-0" />
                                                        <span class="text-xs">F</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <!-- Age -->
                                            <td class="border-r-2 border-gray-800 px-2 py-2 text-center align-middle w-14">
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
                                                    class="w-full border-b border-gray-300 bg-transparent text-xs text-gray-800 focus:outline-none focus:border-gray-600 py-0.5" />
                                            </td>
                                            <!-- Remarks + remove button -->
                                            <td class="px-2 py-2 align-middle">
                                                <div class="flex items-center gap-1">
                                                    <input v-model="members[rowIndex-1].remarks"
                                                        placeholder="Remarks"
                                                        class="flex-1 border-b border-gray-300 bg-transparent text-xs text-gray-800 focus:outline-none focus:border-gray-600 py-0.5" />
                                                    <button v-if="rowIndex > 1"
                                                        @click="removeRow(rowIndex - 1)"
                                                        class="text-red-400 hover:text-red-600 font-bold text-xs shrink-0 ml-1">✕</button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Empty row — transparent borders -->
                                        <tr v-else class="border-b border-gray-200 h-12 cursor-pointer hover:bg-gray-50/40 transition"
                                            @click="rowIndex <= members.length + 1 ? addRow() : null">
                                            <td class="border-r border-gray-200 px-2 text-center align-middle font-bold text-gray-400">{{ rowIndex }}</td>
                                            <td class="border-r border-gray-200 px-2 text-center align-middle">
                                                <span v-if="rowIndex === members.length + 1"
                                                    class="text-xs text-gray-300 italic">+ click to add member</span>
                                            </td>
                                            <td class="border-r border-gray-200"></td>
                                            <td class="border-r border-gray-200"></td>
                                            <td class="border-r border-gray-200"></td>
                                            <td class="border-r border-gray-200"></td>
                                            <td></td>
                                        </tr>
                                    </template>

                                    <!-- Extra rows beyond 6 -->
                                    <tr v-for="(m, extraIdx) in members.slice(6)" :key="`extra-${extraIdx}`"
                                        class="border-b-2 border-gray-800 hover:bg-gray-50/50 transition">
                                        <td class="border-r-2 border-gray-800 px-2 py-2 text-center align-middle font-bold text-gray-700">{{ extraIdx + 7 }}</td>
                                        <td class="border-r-2 border-gray-800 px-2 py-2 align-top">
                                            <input v-model="m.surname" placeholder="Surname *"
                                                class="w-full border-b border-gray-300 bg-transparent text-xs text-gray-800 focus:outline-none py-0.5 mb-1" />
                                            <input v-model="m.first_name" placeholder="First Name *"
                                                class="w-full border-b border-gray-300 bg-transparent text-xs text-gray-800 focus:outline-none py-0.5 mb-1" />
                                            <input v-model="m.middle_name" placeholder="Middle Name"
                                                class="w-full border-b border-gray-300 bg-transparent text-xs text-gray-700 focus:outline-none py-0.5" />
                                        </td>
                                        <td class="border-r-2 border-gray-800 px-2 py-2 align-top">
                                            <input v-model="m.town_city" placeholder="Town / City *"
                                                class="w-full border-b border-gray-300 bg-transparent text-xs text-gray-800 focus:outline-none py-0.5 mb-1" />
                                            <input v-model="m.country" placeholder="Country"
                                                class="w-full border-b border-gray-300 bg-transparent text-xs text-gray-800 focus:outline-none py-0.5" />
                                        </td>
                                        <td class="border-r-2 border-gray-800 px-2 py-2 text-center align-middle">
                                            <div class="flex flex-col gap-1.5 items-center">
                                                <label class="flex items-center gap-1 cursor-pointer">
                                                    <input type="radio" :name="`pub-sex-extra-${extraIdx}`" value="M" v-model="m.sex" class="focus:ring-0" />
                                                    <span class="text-xs">M</span>
                                                </label>
                                                <label class="flex items-center gap-1 cursor-pointer">
                                                    <input type="radio" :name="`pub-sex-extra-${extraIdx}`" value="F" v-model="m.sex" class="focus:ring-0" />
                                                    <span class="text-xs">F</span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="border-r-2 border-gray-800 px-2 py-2 text-center align-middle">
                                            <input v-model="m.age" type="number" min="0" max="120" placeholder="—"
                                                style="color:#111 !important;-moz-appearance:textfield;font-size:13px;font-weight:600;"
                                                class="w-full border-b border-gray-300 bg-transparent text-xs text-center focus:outline-none py-0.5 mb-1 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                            <span v-if="m.visitor_category"
                                                class="inline-block text-xs bg-blue-50 border border-blue-200 text-blue-700 font-semibold px-1 py-0.5 rounded-full">
                                                {{ m.visitor_category }}
                                            </span>
                                        </td>
                                        <td class="border-r-2 border-gray-800 px-2 py-2 align-middle">
                                            <input v-model="m.contact_number" type="tel" placeholder="09xxxxxxxxx"
                                                class="w-full border-b border-gray-300 bg-transparent text-xs text-gray-800 focus:outline-none py-0.5" />
                                        </td>
                                        <td class="px-2 py-2 align-middle">
                                            <div class="flex items-center gap-1">
                                                <input v-model="m.remarks" placeholder="Remarks"
                                                    class="flex-1 border-b border-gray-300 bg-transparent text-xs text-gray-800 focus:outline-none py-0.5" />
                                                <button @click="removeRow(extraIdx + 6)"
                                                    class="text-red-400 hover:text-red-600 font-bold text-xs shrink-0 ml-1">✕</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- ── Footer: Signatures ── -->
                        <div class="border-t-2 border-gray-800 flex">
                            <div class="flex-1 px-4 py-3 space-y-2">
                                <div class="flex items-end gap-3">
                                    <span class="text-xs font-bold text-gray-800">GUEST SIGNATURE:</span>
                                    <div class="w-40 border-b border-gray-700 mb-0.5"></div>
                                </div>
                                <p class="text-xs text-red-600 italic">
                                    NOTE: Buruanganon guest required to fill out this form for monitoring of Statistics only. Thank you
                                </p>
                            </div>
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

                    <!-- Submit button — outside the form -->
                    <div class="mt-4 flex items-center justify-between">
                        <span v-if="isGroup" class="text-sm font-bold bg-gray-800 text-white px-3 py-1.5 rounded-full">
                            Group · {{ activeMemberCount }} person(s)
                        </span>
                        <div v-else></div>
                        <button @click="submit" :disabled="groupForm.processing"
                            class="bg-gray-900 text-white font-bold py-3 px-10 rounded-xl disabled:opacity-50 text-sm hover:bg-black transition shadow-lg">
                            {{ groupForm.processing
                                ? 'Submitting...'
                                : (isGroup ? `Pre-Register ${activeMemberCount} Visitor(s) →` : 'Submit Pre-Registration →') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>