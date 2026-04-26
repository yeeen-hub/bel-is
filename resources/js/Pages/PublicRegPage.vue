<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/LandingLayout.vue'
import DestinationChecklist from '@/Components/DestinationChecklist.vue'

const props = defineProps({
    feeCategories:       { type: Array, default: () => [] },
    barangayAttractions: { type: Array, default: () => [] },
})

const purposeOptions  = ['Tourism', 'Research', 'Event', 'Official Visit', 'Other']
const durationOptions = ['1 day', '2 days', '3 days', '4-7 days', 'More than 1 week']

const mode = ref('single')
const openPurpose  = ref(false)
const openDuration = ref(false)
const openCategory = ref(false)

// ── Flash ─────────────────────────────────────────────────────────────────────
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

// ── Category helper ───────────────────────────────────────────────────────────
const categoryLabel = (cat) => cat?.age_range
    ? `${cat.category} (${cat.age_range}) — ₱${cat.fee}`
    : `${cat?.category ?? ''} — ₱${cat?.fee ?? ''}`

// ── Individual form ───────────────────────────────────────────────────────────
const form = useForm({
    first_name:       '',
    last_name:        '',
    municipality:     '',
    province:         '',
    purpose:          '',
    purpose_other:    '',
    duration_of_stay: '',
    contact_number:   '',
    visitor_category: '',
    destinations:     [],
})

const submit = () => {
    form.post(route('pre-register.store'), {
        preserveScroll: true,
        onSuccess: () => window.scrollTo({ top: 0, behavior: 'smooth' })
    })
}

// ── Group form ────────────────────────────────────────────────────────────────
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
    destinations:     [],
    openPurpose:      false,
    openDuration:     false,
    openCategory:     false,
})

const members   = ref([blankMember()])
const groupForm = useForm({ members: [] })

const memberCount  = computed(() => members.value.length)
const addMember    = () => members.value.push(blankMember())
const removeMember = (i) => { if (members.value.length > 1) members.value.splice(i, 1) }

const cloneFromLeader = (i) => {
    const leader = members.value[0]
    const m      = members.value[i]
    m.municipality     = leader.municipality
    m.province         = leader.province
    m.purpose          = leader.purpose
    m.purpose_other    = leader.purpose_other
    m.duration_of_stay = leader.duration_of_stay
    // destinations intentionally NOT cloned — each member picks their own
}

const memberErrors = ref([])
const validateMembers = () => {
    memberErrors.value = members.value.map(m => {
        const e = {}
        if (!m.first_name.trim())   e.first_name        = 'First name is required.'
        if (!m.last_name.trim())    e.last_name         = 'Last name is required.'
        if (!m.municipality.trim()) e.municipality      = 'Municipality is required.'
        if (!m.province.trim())     e.province          = 'Province is required.'
        if (!m.purpose)             e.purpose           = 'Purpose is required.'
        if (m.purpose === 'Other' && !m.purpose_other?.trim())
                                    e.purpose_other     = 'Please specify the purpose.'
        if (!m.duration_of_stay)    e.duration_of_stay  = 'Duration is required.'
        if (!m.visitor_category)    e.visitor_category  = 'Category is required.'
        return e
    })
    return memberErrors.value.every(e => Object.keys(e).length === 0)
}

const submitGroup = () => {
    if (!validateMembers()) return
    groupForm.members = members.value.map(m => ({
        first_name:       m.first_name,
        last_name:        m.last_name,
        municipality:     m.municipality,
        province:         m.province,
        purpose:          m.purpose,
        purpose_other:    m.purpose === 'Other' ? m.purpose_other : '',
        duration_of_stay: m.duration_of_stay,
        contact_number:   m.contact_number || '',
        visitor_category: m.visitor_category,
        destinations:     m.destinations,
    }))
    groupForm.post(route('pre-register.group'), {
        preserveScroll: true,
        onSuccess: () => window.scrollTo({ top: 0, behavior: 'smooth' })
    })
}
</script>

<template>
    <LandingLayout>
        <div class="min-h-screen bg-gray-50">

            <!-- ══════════ SUCCESS SCREEN ══════════ -->
            <div v-if="submitted" class="px-4 py-16">

                <div v-if="flashMode === 'single'" class="flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 max-w-md w-full text-center">
                        <div class="flex justify-center mb-4">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z" />
                                </svg>
                            </div>
                        </div>
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
                            <img :src="qrUrl(referenceCode)" :alt="referenceCode" class="w-44 h-44 border border-gray-200 rounded-xl p-2" />
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-6 text-left">
                            <p class="text-sm font-semibold text-amber-800">📸 Screenshot this screen</p>
                            <p class="text-xs text-amber-700 mt-1">Show your reference code or QR at the Bel-is Tourism Hub checkpoint. Pay the environmental fee based on your selected category to complete your entry.</p>
                        </div>
                        <Link :href="route('home')" class="block w-full bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-gray-700 transition text-sm text-center">
                            Back to Bel-is Website
                        </Link>
                    </div>
                </div>

                <div v-else-if="flashMode === 'group'" class="flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 max-w-md w-full text-center">
                        <div class="flex justify-center mb-4">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z" />
                                </svg>
                            </div>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-800 mb-1">Your Group is Pre-Registered!</h1>
                        <p class="text-gray-500 text-sm mb-6">{{ groupMembers.length }} member(s) registered. Show this code at the checkpoint.</p>
                        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl px-6 py-5 mb-6">
                            <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Group Reference Code</p>
                            <p class="text-4xl font-mono font-bold text-gray-900 tracking-widest">{{ groupCode }}</p>
                        </div>
                        <div class="flex flex-col items-center mb-6">
                            <p class="text-xs text-gray-400 mb-3">Or let staff scan this QR code</p>
                            <img :src="qrUrl(groupCode)" :alt="groupCode" class="w-44 h-44 border border-gray-200 rounded-xl p-2" />
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-6 text-left">
                            <p class="text-sm font-semibold text-amber-800">📸 Screenshot this screen</p>
                            <p class="text-xs text-amber-700 mt-1">Staff will look up all {{ groupMembers.length }} member(s) and collect fees per person.</p>
                        </div>
                        <Link :href="route('home')" class="block w-full bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-gray-700 transition text-sm text-center">
                            Back to Bel-is Website
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ══════════ REGISTRATION FORM ══════════ -->
            <div v-else class="pt-28 pb-12 px-4">





                <!-- Header -->
                <div class="max-w-2xl mx-auto text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Visitor Pre-Registration</h1>
                    <p class="text-gray-500 text-sm mt-2 max-w-md mx-auto">
                        Fill out this form before arriving at Barangay Bel-is. You will receive a reference code to show at the checkpoint.
                    </p>
                </div>

                <!-- Mode Toggle -->
                <div class="max-w-2xl mx-auto mb-6">
                    <div class="flex gap-1 bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
                        <button type="button" @click="mode = 'single'"
                            :class="mode === 'single' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all">Individual</button>
                        <button type="button" @click="mode = 'group'"
                            :class="mode === 'group' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold transition-all">
                            Group
                            <span v-if="mode === 'group'" class="ml-1.5 text-xs font-bold bg-white text-gray-900 px-1.5 py-0.5 rounded-full">{{ memberCount }}</span>
                        </button>
                    </div>
                </div>

                <!-- Privacy Notice -->
                <div class="max-w-2xl mx-auto mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-blue-800">Privacy Notice (RA 10173)</p>
                                <p class="text-xs text-blue-700 mt-1 leading-relaxed">Your personal information is collected solely for tourism management and environmental fee recording by Barangay Bel-is, Buruanga, Aklan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── INDIVIDUAL FORM ── -->
                <div v-if="mode === 'single'" class="max-w-2xl mx-auto">
                    <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-1.5">First Name *</label>
                                <input v-model="form.first_name" type="text" autocomplete="off"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-gray-300 focus:outline-none" />
                                <p v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-1.5">Last Name *</label>
                                <input v-model="form.last_name" type="text" autocomplete="off"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-gray-300 focus:outline-none" />
                                <p v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-1.5">Municipality *</label>
                                <input v-model="form.municipality" type="text"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-gray-300 focus:outline-none" />
                                <p v-if="form.errors.municipality" class="text-red-500 text-xs mt-1">{{ form.errors.municipality }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-1.5">Province *</label>
                                <input v-model="form.province" type="text"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-gray-300 focus:outline-none" />
                                <p v-if="form.errors.province" class="text-red-500 text-xs mt-1">{{ form.errors.province }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">Phone Number (optional)</label>
                            <input v-model="form.contact_number" type="tel"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-gray-300 focus:outline-none" />
                        </div>

                        <!-- Visitor Category -->
                        <div class="relative">
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">Visitor Category *</label>
                            <button type="button" @mousedown.prevent @click="openCategory = !openCategory"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm flex items-center justify-between">
                                <span :class="form.visitor_category ? 'text-gray-800' : 'text-gray-400'">
                                    {{ form.visitor_category
                                        ? categoryLabel(feeCategories.find(c => c.category === form.visitor_category))
                                        : 'Select your category' }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <ul v-show="openCategory" class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg">
                                <li v-for="cat in feeCategories" :key="cat.id"
                                    @mousedown.prevent @click="form.visitor_category = cat.category; openCategory = false"
                                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer text-sm border-b last:border-0 flex items-center justify-between"
                                    :class="form.visitor_category === cat.category ? 'bg-gray-50 font-semibold' : ''">
                                    <div>
                                        <span class="font-medium text-gray-800">{{ cat.category }}</span>
                                        <span v-if="cat.age_range" class="text-gray-400 text-xs ml-2">{{ cat.age_range }}</span>
                                    </div>
                                    <span class="text-green-700 font-bold text-xs">₱{{ cat.fee }}</span>
                                </li>
                            </ul>
                            <p v-if="form.errors.visitor_category" class="text-red-500 text-xs mt-1">{{ form.errors.visitor_category }}</p>
                            <div v-if="form.visitor_category" class="mt-2">
                                <span class="inline-flex items-center gap-1.5 bg-green-50 border border-green-200 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                                    Environmental fee: ₱{{ feeCategories.find(c => c.category === form.visitor_category)?.fee ?? '—' }} to be paid at the checkpoint
                                </span>
                            </div>
                        </div>

                        <!-- Destination Checklist -->
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">
                                Where are you going?
                                <span class="text-gray-400 font-normal ml-1">(optional — check all that apply)</span>
                            </label>
                            <DestinationChecklist
                                v-model="form.destinations"
                                :attractions="barangayAttractions"
                            />
                        </div>

                        <!-- Purpose & Duration -->
                        <div class="grid grid-cols-2 gap-5">
                            <div class="relative">
                                <label class="block text-gray-700 text-sm font-semibold mb-1.5">Purpose *</label>
                                <button type="button" @mousedown.prevent @click="openPurpose = !openPurpose"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm">
                                    <span :class="form.purpose ? 'text-gray-800' : 'text-gray-400'">{{ form.purpose || 'Select purpose' }}</span>
                                </button>
                                <ul v-show="openPurpose" class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg">
                                    <li v-for="opt in purposeOptions" :key="opt"
                                        @mousedown.prevent @click="form.purpose = opt; openPurpose = false"
                                        class="px-4 py-2 hover:bg-gray-50 cursor-pointer text-sm">{{ opt }}</li>
                                </ul>
                                <p v-if="form.errors.purpose" class="text-red-500 text-xs mt-1">{{ form.errors.purpose }}</p>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-700 text-sm font-semibold mb-1.5">Duration *</label>
                                <button type="button" @mousedown.prevent @click="openDuration = !openDuration"
                                    class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-left bg-white text-sm">
                                    <span :class="form.duration_of_stay ? 'text-gray-800' : 'text-gray-400'">{{ form.duration_of_stay || 'Select duration' }}</span>
                                </button>
                                <ul v-show="openDuration" class="absolute z-10 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg">
                                    <li v-for="opt in durationOptions" :key="opt"
                                        @mousedown.prevent @click="form.duration_of_stay = opt; openDuration = false"
                                        class="px-4 py-2 hover:bg-gray-50 cursor-pointer text-sm">{{ opt }}</li>
                                </ul>
                                <p v-if="form.errors.duration_of_stay" class="text-red-500 text-xs mt-1">{{ form.errors.duration_of_stay }}</p>
                            </div>
                        </div>

                        <div v-if="form.purpose === 'Other'">
                            <label class="block text-gray-700 text-sm font-semibold mb-1.5">Please specify *</label>
                            <input v-model="form.purpose_other" type="text"
                                class="w-full border border-gray-200 rounded-lg py-2.5 px-4 text-sm focus:ring-2 focus:ring-gray-300 focus:outline-none" />
                            <p v-if="form.errors.purpose_other" class="text-red-500 text-xs mt-1">{{ form.errors.purpose_other }}</p>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-gray-900 text-white font-bold py-3 rounded-xl disabled:opacity-50 hover:bg-gray-700 transition">
                            {{ form.processing ? 'Submitting...' : 'Submit Pre-Registration →' }}
                        </button>
                    </form>
                </div>

                <!-- ── GROUP FORM ── -->
                <div v-else class="max-w-2xl mx-auto space-y-4">
                    <div v-for="(m, i) in members" :key="i"
                        class="bg-white rounded-2xl border shadow-sm"
                        :class="i === 0 ? 'border-gray-800' : 'border-gray-200'">

                        <div class="flex items-center justify-between px-5 py-3 rounded-t-2xl"
                            :class="i === 0 ? 'bg-gray-900 text-white' : 'bg-gray-50'">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ i === 0 ? '★ Group Leader' : `Member ${i + 1}` }}</span>
                            <div class="flex items-center gap-2">
                                <button v-if="i > 0" type="button" @click="cloneFromLeader(i)" class="text-xs text-blue-500 font-bold border border-blue-200 px-2 py-1 rounded">Clone Location</button>
                                <button v-if="i > 0" type="button" @click="removeMember(i)" class="text-xs text-red-400 font-bold px-2 py-1">Remove</button>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <input v-model="m.first_name" placeholder="First Name"
                                        class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-gray-300 focus:outline-none" />
                                    <p v-if="memberErrors[i]?.first_name" class="text-red-500 text-xs mt-1">{{ memberErrors[i].first_name }}</p>
                                </div>
                                <div>
                                    <input v-model="m.last_name" placeholder="Last Name"
                                        class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-gray-300 focus:outline-none" />
                                    <p v-if="memberErrors[i]?.last_name" class="text-red-500 text-xs mt-1">{{ memberErrors[i].last_name }}</p>
                                </div>
                                <div>
                                    <input v-model="m.municipality" placeholder="Municipality"
                                        class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-gray-300 focus:outline-none" />
                                    <p v-if="memberErrors[i]?.municipality" class="text-red-500 text-xs mt-1">{{ memberErrors[i].municipality }}</p>
                                </div>
                                <div>
                                    <input v-model="m.province" placeholder="Province"
                                        class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-gray-300 focus:outline-none" />
                                    <p v-if="memberErrors[i]?.province" class="text-red-500 text-xs mt-1">{{ memberErrors[i].province }}</p>
                                </div>
                            </div>

                            <!-- Visitor Category -->
                            <div class="relative">
                                <label class="block text-gray-600 text-xs font-semibold mb-1.5">Visitor Category *</label>
                                <button type="button" @click="m.openCategory = !m.openCategory"
                                    class="w-full border border-gray-200 rounded-lg p-2.5 text-sm bg-white text-left flex items-center justify-between focus:outline-none">
                                    <span :class="m.visitor_category ? 'text-gray-800' : 'text-gray-400'">
                                        {{ m.visitor_category
                                            ? categoryLabel(feeCategories.find(c => c.category === m.visitor_category))
                                            : 'Select category' }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul v-show="m.openCategory" class="absolute z-20 w-full mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-44 overflow-auto">
                                    <li v-for="cat in feeCategories" :key="cat.id"
                                        @click="m.visitor_category = cat.category; m.openCategory = false"
                                        class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm border-b last:border-0 flex items-center justify-between"
                                        :class="m.visitor_category === cat.category ? 'bg-gray-50 font-semibold' : ''">
                                        <div>
                                            <span class="font-medium">{{ cat.category }}</span>
                                            <span v-if="cat.age_range" class="text-gray-400 text-xs ml-2">{{ cat.age_range }}</span>
                                        </div>
                                        <span class="text-green-700 font-bold text-xs">₱{{ cat.fee }}</span>
                                    </li>
                                </ul>
                                <p v-if="memberErrors[i]?.visitor_category" class="text-red-500 text-xs mt-1">{{ memberErrors[i].visitor_category }}</p>
                                <div v-if="m.visitor_category" class="mt-1.5">
                                    <span class="inline-flex items-center gap-1 bg-green-50 border border-green-200 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                        ₱{{ feeCategories.find(c => c.category === m.visitor_category)?.fee ?? '—' }} at checkpoint
                                    </span>
                                </div>
                            </div>

                            <!-- Destination per member -->
                            <div>
                                <label class="block text-gray-600 text-xs font-semibold mb-2">
                                    Where are you going? <span class="text-gray-400 font-normal">(optional)</span>
                                </label>
                                <DestinationChecklist
                                    v-model="m.destinations"
                                    :attractions="barangayAttractions"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <select v-model="m.purpose" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm bg-white focus:outline-none">
                                        <option value="" disabled>Select Purpose</option>
                                        <option v-for="o in purposeOptions" :key="o">{{ o }}</option>
                                    </select>
                                    <p v-if="memberErrors[i]?.purpose" class="text-red-500 text-xs mt-1">{{ memberErrors[i].purpose }}</p>
                                </div>
                                <div>
                                    <select v-model="m.duration_of_stay" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm bg-white focus:outline-none">
                                        <option value="" disabled>Select Duration</option>
                                        <option v-for="o in durationOptions" :key="o">{{ o }}</option>
                                    </select>
                                    <p v-if="memberErrors[i]?.duration_of_stay" class="text-red-500 text-xs mt-1">{{ memberErrors[i].duration_of_stay }}</p>
                                </div>
                            </div>

                            <div v-if="m.purpose === 'Other'">
                                <input v-model="m.purpose_other" placeholder="Please specify purpose"
                                    class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-gray-300 focus:outline-none" />
                                <p v-if="memberErrors[i]?.purpose_other" class="text-red-500 text-xs mt-1">{{ memberErrors[i].purpose_other }}</p>
                            </div>

                            <input v-model="m.contact_number" placeholder="Phone (optional)"
                                class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-gray-300 focus:outline-none" />
                        </div>
                    </div>

                    <button type="button" @click="addMember"
                        class="w-full py-4 border-2 border-dashed border-gray-300 rounded-2xl text-gray-500 font-bold hover:bg-white transition">
                        + Add Member
                    </button>

                    <!-- Validation summary -->
                    <div v-if="memberErrors.length && memberErrors.some(e => Object.keys(e).length > 0)"
                        class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                        <p class="font-semibold mb-1">Please fix the following:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-xs">
                            <li v-for="(e, i) in memberErrors" :key="i">
                                <span v-if="Object.keys(e).length > 0">
                                    <strong>{{ i === 0 ? 'Group Leader' : `Member ${i + 1}` }}</strong>
                                    — {{ Object.values(e).join(', ') }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700">{{ memberCount }} visitor(s) in group</p>
                        <button type="button" @click="submitGroup" :disabled="groupForm.processing"
                            class="bg-gray-900 text-white font-bold py-2.5 px-6 rounded-xl disabled:opacity-50 hover:bg-gray-700 transition">
                            {{ groupForm.processing ? 'Submitting...' : 'Pre-Register Group →' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </LandingLayout>
</template>