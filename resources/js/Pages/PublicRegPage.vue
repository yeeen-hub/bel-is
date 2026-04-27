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
const submitted     = computed(() => {
    const s = flash.value.success
    return s === true || s === 'true' || s === 1 || s === '1'
})
const flashMode     = computed(() => flash.value.mode ?? 'single')
const referenceCode = computed(() => flash.value.reference_code ?? '')
const fullName      = computed(() => flash.value.full_name ?? '')
const groupMembers  = computed(() => flash.value.members ?? [])
const groupCode     = computed(() => flash.value.group_code ?? '')
const qrUrl = (code) =>
    code ? `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(code)}` : ''

// ── Wizard steps ──────────────────────────────────────────────────────────────
// Step 1: Who's visiting? (name, sex, age, category)
// Step 2: Where are you from? (town/city, country, nationality)
// Step 3: Visit details (destination, duration, purpose)
// Step 4: Additional info (contact, remarks) + group members
// Step 5: Review & Submit
const currentStep = ref(1)
const totalSteps  = 5

const stepTitles = [
    'Who\'s visiting?',
    'Where are you from?',
    'Visit details',
    'Additional info',
    'Review & Submit',
]

const stepIcons = ['', '', '', '', '']

const goNext = () => { if (currentStep.value < totalSteps) currentStep.value++ }
const goPrev = () => { if (currentStep.value > 1) currentStep.value-- }
const goTo   = (step) => { if (step <= furthestStep.value) currentStep.value = step }

const furthestStep = ref(1)
watch(currentStep, (v) => { if (v > furthestStep.value) furthestStep.value = v })

// ── Validation errors per step ────────────────────────────────────────────────
const errors = ref({})

const validateStep = (step) => {
    errors.value = {}
    const m = members.value[0]

    if (step === 1) {
        if (!m.surname.trim())    errors.value.surname    = 'Last name is required'
        if (!m.first_name.trim()) errors.value.first_name = 'First name is required'
        if (!m.sex)               errors.value.sex        = 'Please select your gender'
    }

    if (step === 2) {
        if (!m.town_city.trim())  errors.value.town_city  = 'Town/City is required'
        if (!m.nationality)       errors.value.nationality = 'Please select your nationality'
    }

    if (step === 3) {
        if (!sharedPurpose.value) errors.value.purpose = 'Please select a purpose of visit'
        if (!sharedIsDayTour.value && !sharedNights.value) {
            errors.value.nights = 'Please enter number of nights'
        }
    }

    if (step === 4) {
        // Validate group members if group is enabled
        if (showGroup.value) {
            members.value.slice(1).forEach((member, idx) => {
                if (!member.surname.trim() && !member.first_name.trim()) return // skip empty rows
                if (!member.surname.trim())   errors.value[`member_${idx}_surname`]    = 'Last name required'
                if (!member.first_name.trim()) errors.value[`member_${idx}_firstname`] = 'First name required'
                if (!member.town_city?.trim()) errors.value[`member_${idx}_town`]      = 'Town/City required'
            })
        }
    }

    // Scroll to first error and shake
    if (Object.keys(errors.value).length > 0) {
        window.scrollTo({ top: 0, behavior: 'smooth' })
        shakeErrors.value = true
        setTimeout(() => { shakeErrors.value = false }, 600)
    }

    return Object.keys(errors.value).length === 0
}

const shakeErrors = ref(false)

const nextStep = () => {
    if (validateStep(currentStep.value)) goNext()
    else window.scrollTo({ top: 0, behavior: 'smooth' })
}

// ── Shared top-level fields ───────────────────────────────────────────────────
const sharedPurpose   = ref('Tourism')
const sharedPurposeOther = ref('')
const sharedIsDayTour = ref(true)
const sharedNights    = ref('')
const sharedDuration  = computed(() =>
    sharedIsDayTour.value ? 'Day Tour' : (sharedNights.value ? `${sharedNights.value} night(s)` : '')
)
const sharedDestinations = ref([])
const sharedCategory  = ref('')

// ── Age/category helpers ──────────────────────────────────────────────────────
const deriveCategory = (age) => {
    if (!age || isNaN(age)) return ''
    const n = parseInt(age)
    for (const cat of props.feeCategories) {
        const r = cat.age_range?.toLowerCase() ?? ''
        if (!/\d/.test(r)) continue
        if (r.includes('above') || r.includes('abov')) {
            const min = parseInt(r.match(/(\d+)/)?.[1] ?? '')
            if (!isNaN(min) && n >= min) return cat.category
        }
        if (r.includes('below')) {
            const max = parseInt(r.match(/(\d+)/)?.[1] ?? '')
            if (!isNaN(max) && n <= max) return cat.category
        }
        const m = r.match(/(\d+)\s*[-–]\s*(\d+)/)
        if (m && n >= parseInt(m[1]) && n <= parseInt(m[2])) return cat.category
    }
    return ''
}

const ageInCategoryRange = (age, categoryName) => {
    const cat = props.feeCategories.find(c => c.category === categoryName)
    if (!cat) return null
    const r = cat.age_range?.toLowerCase() ?? ''
    if (!/\d/.test(r)) return null
    const n = parseInt(age)
    if (isNaN(n)) return false
    if (r.includes('above') || r.includes('abov')) {
        const min = parseInt(r.match(/(\d+)/)?.[1] ?? '')
        return !isNaN(min) && n >= min
    }
    if (r.includes('below')) {
        const max = parseInt(r.match(/(\d+)/)?.[1] ?? '')
        return !isNaN(max) && n <= max
    }
    const m = r.match(/(\d+)\s*[-–]\s*(\d+)/)
    if (m) return n >= parseInt(m[1]) && n <= parseInt(m[2])
    return null
}

const deriveNationality = (country) => {
    if (!country) return ''
    const c = country.toLowerCase().trim()
    if (c === 'philippines' || c === 'ph' || c === '') return 'Local'
    if (c.includes('aklan') || c.includes('buruanga')) return 'Aklanon'
    return 'Foreign'
}

// ── Members ───────────────────────────────────────────────────────────────────
const blankMember = () => ({
    surname: '', first_name: '', middle_name: '',
    town_city: '', country: '', sex: '', age: '',
    visitor_category: '', contact_number: '', nationality: '', remarks: '',
})

const members   = ref([blankMember()])
const showGroup = ref(false)

const addMember    = () => members.value.push(blankMember())
const removeMember = (i) => { if (members.value.length > 1) members.value.splice(i, 1) }

const isGroup = computed(() =>
    members.value.length > 1 &&
    members.value.slice(1).some(m => m.surname.trim() || m.first_name.trim())
)

// Watch age → category for each member
const setupMemberWatches = (i) => {
    watch(() => members.value[i]?.age, (age) => {
        if (!members.value[i]) return
        const derived = deriveCategory(age)
        if (derived) members.value[i].visitor_category = derived
        if (i === 0 && derived) sharedCategory.value = derived
    })
    watch(() => members.value[i]?.visitor_category, (newCat, oldCat) => {
        if (!members.value[i] || !newCat || newCat === oldCat) return
        const age = members.value[i].age
        if (!age && age !== 0) return
        if (ageInCategoryRange(age, newCat) === false) members.value[i].age = ''
    })
    watch(() => members.value[i]?.country, (country) => {
        if (!members.value[i] || members.value[i]._nationalityManuallySet) return
        members.value[i].nationality = deriveNationality(country)
    })
}

members.value.forEach((_, i) => setupMemberWatches(i))
watch(() => members.value.length, (len) => setupMemberWatches(len - 1))

watch(sharedCategory, (newCat) => {
    if (!newCat || !members.value[0]) return
    const age = members.value[0].age
    if (!age && age !== 0) return
    if (ageInCategoryRange(age, newCat) === false) {
        members.value[0].age = ''
        members.value[0].visitor_category = ''
    }
})

// ── Build payload ─────────────────────────────────────────────────────────────
const singleForm = useForm({
    first_name: '', last_name: '', middle_name: '',
    town_city: '', country: '', sex: '', age: null,
    visitor_category: '', contact_number: '', nationality: '', remarks: '',
    purpose: '', purpose_other: null, duration_of_stay: '',
    is_day_tour: true, nights: null, destinations: [],
    municipality: '', province: '', place_of_origin: '',
})

const groupForm = useForm({ members: [] })

const buildPayload = (m) => ({
    first_name:       m.first_name.trim(),
    last_name:        m.surname.trim(),
    middle_name:      m.middle_name?.trim() || null,
    town_city:        m.town_city.trim(),
    country:          m.country.trim() || 'Philippines',
    sex:              m.sex || null,
    age:              m.age ? parseInt(m.age) : null,
    visitor_category: m.visitor_category || sharedCategory.value || null,
    contact_number:   m.contact_number?.trim() || null,
    nationality:      m.nationality || 'Local',
    remarks:          m.remarks?.trim() || null,
    purpose:          sharedPurpose.value,
    purpose_other:    sharedPurpose.value === 'Other' ? sharedPurposeOther.value : null,
    duration_of_stay: sharedDuration.value,
    is_day_tour:      sharedIsDayTour.value,
    nights:           sharedIsDayTour.value ? null : (parseInt(sharedNights.value) || null),
    destinations:     sharedDestinations.value,
    municipality:     m.town_city.trim(),
    province:         m.country.trim() || 'Philippines',
    place_of_origin:  `${m.town_city.trim()}, ${m.country.trim() || 'Philippines'}`,
})

const isSubmitting = computed(() => singleForm.processing || groupForm.processing)

const submit = () => {
    const active = members.value.filter((m, i) => i === 0 || m.surname.trim() || m.first_name.trim())
    const payload = buildPayload(active[0])

    if (active.length === 1) {
        const p = buildPayload(active[0])
        singleForm.first_name       = p.first_name
        singleForm.last_name        = p.last_name
        singleForm.middle_name      = p.middle_name
        singleForm.town_city        = p.town_city
        singleForm.country          = p.country
        singleForm.sex              = p.sex
        singleForm.age              = p.age
        singleForm.visitor_category = p.visitor_category
        singleForm.contact_number   = p.contact_number
        singleForm.nationality      = p.nationality
        singleForm.remarks          = p.remarks
        singleForm.purpose          = p.purpose
        singleForm.purpose_other    = p.purpose_other
        singleForm.duration_of_stay = p.duration_of_stay
        singleForm.is_day_tour      = p.is_day_tour
        singleForm.nights           = p.nights
        singleForm.destinations     = p.destinations
        singleForm.municipality     = p.municipality
        singleForm.province         = p.province
        singleForm.place_of_origin  = p.place_of_origin
        singleForm.post(route('pre-register.store'), {
            preserveScroll: false,
            preserveState: false,
            onSuccess: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
            onError: (e) => { console.error('single error', e) }
        })
    } else {
        groupForm.members = active.map(buildPayload)
        groupForm.post(route('pre-register.group'), {
            preserveScroll: false,
            preserveState: false,
            onSuccess: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
            onError: (e) => { console.error('group error', e) }
        })
    }
}

const purposeOptions = ['Tourism', 'Research', 'Event', 'Official Visit', 'Other']

// ── Review summary ────────────────────────────────────────────────────────────
const m0 = computed(() => members.value[0])
const activeMembers = computed(() =>
    members.value.filter((m, i) => i === 0 || m.surname.trim() || m.first_name.trim())
)
</script>

<template>
    <LandingLayout>
        <div class="min-h-screen bg-gray-50 pt-28 pb-12 px-4">

            <!-- ══ SUCCESS SCREEN ══ -->
            <div v-if="submitted" class="flex items-center justify-center py-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 max-w-md w-full text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z"/>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-800 mb-1">You're Pre-Registered!</h2>
                    <p class="text-gray-500 text-sm mb-6">Show this code at the Bel-is Tourism Hub checkpoint.</p>

                    <!-- Single -->
                    <div v-if="flashMode === 'single'" class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl px-6 py-5 mb-6">
                        <p class="text-xs text-gray-400 mb-1">Reference Code</p>
                        <p class="text-3xl font-mono font-bold text-gray-900 tracking-widest mb-3">{{ referenceCode }}</p>
                        <img v-if="referenceCode" :src="qrUrl(referenceCode)" class="mx-auto w-32 h-32" alt="QR Code" />
                        <p class="text-xs text-gray-400 mt-2">{{ fullName }}</p>
                    </div>

                    <!-- Group -->
                    <div v-if="flashMode === 'group'" class="space-y-3 mb-6">
                        <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl px-4 py-3 mb-2 text-center">
                            <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Group Code</p>
                            <p class="text-2xl font-mono font-bold text-gray-900 tracking-widest">{{ groupCode }}</p>
                            <img v-if="groupCode" :src="qrUrl(groupCode)" class="mx-auto w-28 h-28 mt-2" alt="QR" />
                        </div>
                        
                    </div>

                    <p class="text-xs text-gray-400 mb-5">📸 Screenshot this page or note your code before leaving.</p>
                    <Link :href="route('home')"
                        class="inline-block bg-gray-900 text-white text-sm font-bold px-6 py-3 rounded-xl hover:bg-gray-700 transition">
                        Back to Home
                    </Link>
                </div>
            </div>

            <!-- ══ WIZARD FORM ══ -->
            <div v-else class="max-w-xl mx-auto">

                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-800">Visitor Pre-Registration</h1>
                    <p class="text-gray-500 text-sm mt-1">Fill out this form before arriving at Barangay Bel-is.</p>
                </div>

                <!-- Step indicators -->
                <div class="flex items-center justify-between mb-8 px-2">
                    <template v-for="(title, idx) in stepTitles" :key="idx">
                        <button @click="goTo(idx + 1)"
                            class="flex flex-col items-center gap-1 group"
                            :disabled="idx + 1 > furthestStep">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                                :class="currentStep === idx + 1
                                    ? 'bg-gray-900 text-white scale-110 shadow-md'
                                    : idx + 1 < currentStep
                                        ? 'bg-green-500 text-white'
                                        : 'bg-gray-200 text-gray-400'">
                                <span v-if="idx + 1 < currentStep">✓</span>
                                <span v-else>{{ idx + 1 }}</span>
                            </div>
                            <span class="text-xs hidden sm:block"
                                :class="currentStep === idx + 1 ? 'text-gray-800 font-semibold' : 'text-gray-400'">
                                {{ stepIcons[idx] }}
                            </span>
                        </button>
                        <!-- Connector line -->
                        <div v-if="idx < stepTitles.length - 1"
                            class="flex-1 h-0.5 mx-1 transition-colors"
                            :class="idx + 1 < currentStep ? 'bg-green-400' : 'bg-gray-200'"></div>
                    </template>
                </div>

                <!-- Step card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <!-- Step header -->
                    <div class="bg-gray-900 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ stepIcons[currentStep - 1] }}</span>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Step {{ currentStep }} of {{ totalSteps }}</p>
                                <h2 class="text-white font-bold text-lg leading-tight">{{ stepTitles[currentStep - 1] }}</h2>
                            </div>
                        </div>
                    </div>

                   

                    <!-- ── STEP 1: Who's visiting ── -->
                    <div v-if="currentStep === 1" class="px-6 py-6 space-y-5">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Last Name <span class="text-red-400">*</span></label>
                                <input v-model="members[0].surname" type="text" placeholder="e.g. Santos"
                                    @input="delete errors.surname"
                                    :class="['w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 transition',
                                        errors.surname ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-200 focus:ring-gray-400']" />
                                <p v-if="errors.surname" class="text-red-500 text-xs mt-1">{{ errors.surname }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">First Name <span class="text-red-400">*</span></label>
                                <input v-model="members[0].first_name" type="text" placeholder="e.g. Maria"
                                    @input="delete errors.first_name"
                                    :class="['w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 transition',
                                        errors.first_name ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-200 focus:ring-gray-400']" />
                                <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">{{ errors.first_name }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Middle Name <span class="text-gray-400">(optional)</span></label>
                            <input v-model="members[0].middle_name" type="text" placeholder="e.g. Cruz"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">Gender <span class="text-red-400">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <button v-for="opt in [{ val: 'M', label: '♂ Male' }, { val: 'F', label: '♀ Female' }]"
                                    :key="opt.val" type="button"
                                    @click="members[0].sex = opt.val; delete errors.sex"
                                    :class="['border-2 rounded-xl py-3 text-sm font-semibold transition',
                                        members[0].sex === opt.val
                                            ? 'border-gray-900 bg-gray-900 text-white'
                                            : errors.sex
                                                ? 'border-red-300 bg-red-50 text-red-500'
                                                : 'border-gray-200 text-gray-600 hover:border-gray-400']">
                                    {{ opt.label }}
                                </button>
                            </div>
                            <p v-if="errors.sex" class="text-red-500 text-xs mt-1">{{ errors.sex }}</p>
                        </div>

                        <!-- Age + Category -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Age</label>
                                <input v-model="members[0].age" type="number" min="0" max="120" placeholder="e.g. 28"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                <p class="text-xs text-gray-400 mt-1">Auto-fills category</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Category</label>
                                <select v-model="members[0].visitor_category"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 bg-white">
                                    <option value="">— Select —</option>
                                    <option v-for="cat in feeCategories" :key="cat.id" :value="cat.category">
                                        {{ cat.category }}
                                    </option>
                                </select>
                                <p v-if="members[0].visitor_category" class="text-xs text-gray-400 mt-1">
                                    Fee: ₱{{ feeCategories.find(c => c.category === members[0].visitor_category)?.fee ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ── STEP 2: Where are you from ── -->
                    <div v-if="currentStep === 2" class="px-6 py-6 space-y-5">

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Town / City <span class="text-red-400">*</span></label>
                            <input v-model="members[0].town_city" type="text" placeholder="e.g. Kalibo, Iloilo City, Manila"
                                @input="delete errors.town_city"
                                :class="['w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 transition',
                                    errors.town_city ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-200 focus:ring-gray-400']" />
                            <p v-if="errors.town_city" class="text-red-500 text-xs mt-1">{{ errors.town_city }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Country <span class="text-gray-400">(leave blank if Philippines)</span></label>
                            <input v-model="members[0].country" type="text" placeholder="Philippines"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        </div>

                        <!-- Nationality -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">
                                Nationality <span class="text-red-400">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="opt in ['Local', 'Aklanon', 'OFW', 'Foreign']" :key="opt"
                                    type="button"
                                    @click="members[0].nationality = opt; members[0]._nationalityManuallySet = true; delete errors.nationality"
                                    :class="['border-2 rounded-xl py-2.5 text-sm font-semibold transition',
                                        members[0].nationality === opt
                                            ? 'border-gray-900 bg-gray-900 text-white'
                                            : errors.nationality
                                                ? 'border-red-300 bg-red-50 text-red-500'
                                                : 'border-gray-200 text-gray-600 hover:border-gray-400']">
                                    {{ opt }}
                                </button>
                            </div>
                            <p v-if="errors.nationality" class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <span>⚠</span> {{ errors.nationality }}
                            </p>
                            <p v-else class="text-xs text-gray-400 mt-1">Auto-detected from country. You can change it.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Contact Number <span class="text-gray-400">(optional)</span></label>
                            <input v-model="members[0].contact_number" type="text" placeholder="e.g. 09xxxxxxxxx"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        </div>
                    </div>

                    <!-- ── STEP 3: Visit details ── -->
                    <div v-if="currentStep === 3" class="px-6 py-6 space-y-5">

                        <!-- Destination -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">🏖️ Where are you going? <span class="text-gray-400">(optional)</span></label>
                            <DestinationChecklist
                                v-model="sharedDestinations"
                                :attractions="barangayAttractions" />
                        </div>

                        <!-- Duration -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">⏱ Duration of Stay</label>
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <button type="button" @click="sharedIsDayTour = true"
                                    :class="['border-2 rounded-xl py-3 text-sm font-semibold transition',
                                        sharedIsDayTour
                                            ? 'border-gray-900 bg-gray-900 text-white'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-400']">
                                    ☀️ Day Tour
                                </button>
                                <button type="button" @click="sharedIsDayTour = false"
                                    :class="['border-2 rounded-xl py-3 text-sm font-semibold transition',
                                        !sharedIsDayTour
                                            ? 'border-gray-900 bg-gray-900 text-white'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-400']">
                                    🌙 Overnight
                                </button>
                            </div>
                            <div v-if="!sharedIsDayTour">
                                <label class="block text-xs text-gray-500 mb-1">Number of nights <span class="text-red-400">*</span></label>
                                <input v-model="sharedNights" type="number" min="1" placeholder="e.g. 2"
                                    @input="delete errors.nights"
                                    :class="['w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none',
                                        errors.nights ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-200']" />
                                <p v-if="errors.nights" class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                    <span>⚠</span> {{ errors.nights }}
                                </p>
                            </div>
                        </div>

                        <!-- Purpose -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">🎯 Purpose of Visit <span class="text-red-400">*</span></label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="opt in purposeOptions" :key="opt" type="button"
                                    @click="sharedPurpose = opt; delete errors.purpose"
                                    :class="['border-2 rounded-xl py-2.5 text-sm font-semibold transition',
                                        sharedPurpose === opt
                                            ? 'border-gray-900 bg-gray-900 text-white'
                                            : errors.purpose
                                                ? 'border-red-300 bg-red-50 text-red-500 hover:border-red-400'
                                                : 'border-gray-200 text-gray-600 hover:border-gray-400']">
                                    {{ opt }}
                                </button>
                            </div>
                            <div v-if="sharedPurpose === 'Other'" class="mt-3">
                                <input v-model="sharedPurposeOther" type="text" placeholder="Please specify..."
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                            </div>
                            <p v-if="errors.purpose" class="text-red-500 text-xs mt-1">{{ errors.purpose }}</p>
                        </div>
                    </div>

                    <!-- ── STEP 4: Additional info + group ── -->
                    <div v-if="currentStep === 4" class="px-6 py-6 space-y-5">

                        <!-- Remarks -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Remarks / Concerns <span class="text-gray-400">(optional)</span></label>
                            <textarea v-model="members[0].remarks" rows="2" placeholder="Any complaints, concerns, or suggestions..."
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-gray-400"></textarea>
                        </div>

                        <!-- Group toggle -->
                        <div class="border-t pt-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-700">Traveling with others?</p>
                                    <p class="text-xs text-gray-400">Add group members to register together</p>
                                </div>
                                <button type="button" @click="showGroup = !showGroup; if (showGroup && members.length === 1) addMember()"
                                    :class="['relative w-12 h-6 rounded-full transition-colors',
                                        showGroup ? 'bg-gray-900' : 'bg-gray-300']">
                                    <span :class="['absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform',
                                        showGroup ? 'translate-x-6' : 'translate-x-0']"></span>
                                </button>
                            </div>

                            <!-- Group members -->
                            <div v-if="showGroup" class="mt-4 space-y-4">
                                <div v-for="(member, idx) in members.slice(1)" :key="idx"
                                    class="border border-gray-200 rounded-xl p-4 bg-gray-50 relative">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Member {{ idx + 2 }}</p>
                                        <button type="button" @click="removeMember(idx + 1)"
                                            class="text-gray-400 hover:text-red-500 text-sm font-bold">✕</button>
                                    </div>
                                    <!-- Name -->
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <input v-model="member.surname" type="text" placeholder="Last Name *"
                                            @input="delete errors[`member_${idx}_surname`]"
                                            :class="['border rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-1',
                                                errors[`member_${idx}_surname`] ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-200 focus:ring-gray-400']" />
                                        <input v-model="member.first_name" type="text" placeholder="First Name *"
                                            @input="delete errors[`member_${idx}_firstname`]"
                                            :class="['border rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-1',
                                                errors[`member_${idx}_firstname`] ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-200 focus:ring-gray-400']" />
                                    </div>
                                    <!-- Sex / Age / Category -->
                                    <div class="grid grid-cols-3 gap-3 mb-3">
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Sex</label>
                                            <select v-model="member.sex"
                                                class="border border-gray-200 rounded-lg px-2 py-2 text-sm w-full bg-white focus:outline-none focus:ring-1 focus:ring-gray-400">
                                                <option value="">—</option>
                                                <option value="M">Male</option>
                                                <option value="F">Female</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Age</label>
                                            <input v-model="member.age" type="number" min="0" max="120" placeholder="Age"
                                                class="border border-gray-200 rounded-lg px-2 py-2 text-sm w-full focus:outline-none focus:ring-1 focus:ring-gray-400 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Category</label>
                                            <select v-model="member.visitor_category"
                                                class="border border-gray-200 rounded-lg px-2 py-2 text-sm w-full bg-white focus:outline-none focus:ring-1 focus:ring-gray-400">
                                                <option value="">—</option>
                                                <option v-for="cat in feeCategories" :key="cat.id" :value="cat.category">{{ cat.category }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- Town/City + Country -->
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Town / City *</label>
                                            <input v-model="member.town_city" type="text" placeholder="e.g. Kalibo"
                                                @input="delete errors[`member_${idx}_town`]"
                                                :class="['border rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-1',
                                                    errors[`member_${idx}_town`] ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-gray-200 focus:ring-gray-400']" />
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Country</label>
                                            <input v-model="member.country" type="text" placeholder="Philippines"
                                                class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-1 focus:ring-gray-400" />
                                        </div>
                                    </div>
                                    <!-- Nationality + Contact -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Nationality</label>
                                            <select v-model="member.nationality"
                                                class="border border-gray-200 rounded-lg px-2 py-2 text-sm w-full bg-white focus:outline-none focus:ring-1 focus:ring-gray-400">
                                                <option value="">— Auto —</option>
                                                <option value="Local">Local</option>
                                                <option value="Aklanon">Aklanon</option>
                                                <option value="OFW">OFW</option>
                                                <option value="Foreign">Foreign</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-400 mb-1 block">Contact No.</label>
                                            <input v-model="member.contact_number" type="text" placeholder="09xxxxxxxxx"
                                                class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-1 focus:ring-gray-400" />
                                        </div>
                                    </div>
                                </div>

                                <button type="button" @click="addMember"
                                    class="w-full border-2 border-dashed border-gray-300 rounded-xl py-3 text-sm text-gray-500 hover:border-gray-400 hover:text-gray-700 transition font-semibold">
                                    + Add Another Member
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ── STEP 5: Review & Submit ── -->
                    <div v-if="currentStep === 5" class="px-6 py-6 space-y-4">

                        <p class="text-sm text-gray-500">Please review your information before submitting.</p>

                        <!-- Main visitor summary -->
                        <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Name</span>
                                <span class="font-semibold text-gray-800">{{ m0.first_name }} {{ m0.surname }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Gender</span>
                                <span class="font-semibold text-gray-800">{{ m0.sex === 'M' ? 'Male' : m0.sex === 'F' ? 'Female' : '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Age / Category</span>
                                <span class="font-semibold text-gray-800">{{ m0.age || '—' }} / {{ m0.visitor_category || '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">From</span>
                                <span class="font-semibold text-gray-800">{{ m0.town_city }}{{ m0.country ? ', ' + m0.country : '' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nationality</span>
                                <span class="font-semibold text-gray-800">{{ m0.nationality || '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Purpose</span>
                                <span class="font-semibold text-gray-800">{{ sharedPurpose }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Duration</span>
                                <span class="font-semibold text-gray-800">{{ sharedDuration || 'Day Tour' }}</span>
                            </div>
                        </div>

                        <!-- Group members summary -->
                        <div v-if="activeMembers.length > 1" class="space-y-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Group Members</p>
                            <div v-for="(member, idx) in activeMembers.slice(1)" :key="idx"
                                class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2.5 text-sm">
                                <span class="font-semibold text-gray-800">{{ member.first_name }} {{ member.surname }}</span>
                                <span class="text-gray-400 text-xs">{{ member.visitor_category || '—' }}</span>
                            </div>
                        </div>

                        <!-- Privacy notice -->
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                            <p class="text-xs text-blue-700">
                                <strong>Privacy Notice (RA 10173):</strong> Your personal information is collected solely for tourism management and environmental fee recording by Barangay Bel-is, Buruanga, Aklan.
                            </p>
                        </div>

                        <!-- Submit -->
                        <button type="button" @click="submit" :disabled="isSubmitting"
                            class="w-full bg-gray-900 text-white font-bold py-4 rounded-xl hover:bg-gray-700 transition text-sm disabled:opacity-50 flex items-center justify-center gap-2">
                            <span v-if="isSubmitting">Submitting...</span>
                            <span v-else>Submit Pre-Registration →</span>
                        </button>
                    </div>

                    <!-- ── Navigation buttons ── -->
                    <div class="px-6 pb-6 flex justify-between gap-3">
                        <button v-if="currentStep > 1" type="button" @click="goPrev"
                            class="flex-1 border border-gray-300 text-gray-600 font-semibold py-3 rounded-xl hover:bg-gray-50 transition text-sm">
                            ← Back
                        </button>
                        <div v-else class="flex-1"></div>
                        <button v-if="currentStep < totalSteps" type="button" @click="nextStep"
                            class="flex-1 bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-gray-700 transition text-sm">
                            Continue →
                        </button>
                    </div>
                </div>

                <!-- Step label below card -->
                <p class="text-center text-xs text-gray-400 mt-4">
                    Step {{ currentStep }} of {{ totalSteps }} — {{ stepTitles[currentStep - 1] }}
                </p>
            </div>
        </div>
    </LandingLayout>
</template>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    15%       { transform: translateX(-6px); }
    30%       { transform: translateX(6px); }
    45%       { transform: translateX(-4px); }
    60%       { transform: translateX(4px); }
    75%       { transform: translateX(-2px); }
    90%       { transform: translateX(2px); }
}
.animate-shake { animation: shake 0.55s ease-in-out; }
</style>