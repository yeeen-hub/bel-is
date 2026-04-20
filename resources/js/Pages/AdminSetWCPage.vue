<template>
    <LandingLayout>
      <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
                <div class="relative flex-1">
                    <input type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
                </div> 
                <FontAwesomeIcon icon="bell" />
                <FontAwesomeIcon icon="user" />
            </div>
        </div>

        <div class="p-4 mt-4 rounded-lg flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">System Setting</h1>
                    <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
                </div>
                 <input
                        type="text"
                        placeholder="Search..."
                        class="w-25 p-2 rounded-lg border-2 border-gray-200 focus:border-gray-300 focus:ring-0"
                    />
            </div>
        </div>

        <div class="border-b border-gray-300 flex justify-center gap-6">
            <Link :href="route('settings')" :class="navClass('settings')">General Settings</Link>
            <Link :href="route('usermanagement')" :class="navClass('usermanagement')">User Management</Link>
            <Link :href="route('auditlogs')" :class="navClass('auditlogs')">Audit Logs</Link>
            <Link :href="route('websitecontent')" :class="navClass('websitecontent')">Website Content</Link>
            <Link :href="route('virtualtour')" :class="navClass('virtualtour')">Virtual Tour</Link>
            <Link :href="route('securitysettings')" :class="navClass('securitysettings')">Security</Link>
        </div>

        <!-- Success flash message — auto-dismisses after 4 seconds -->
        <transition name="fade">
            <div v-if="showFlash"
                 class="max-w-6xl mx-auto mt-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm flex items-center justify-between">
                <span>{{ $page.props.flash?.success }}</span>
                <button @click="showFlash = false" class="ml-4 text-green-600 hover:text-green-900 font-bold text-lg leading-none">&times;</button>
            </div>
        </transition>

        <div class="flex items-center justify-between mt-5 p-4">
            <div>
                <div class="flex justify-center mt-2 gap-2">
                    <button @click="activeTab = 'home'"        :class="tabClass('home')">Home</button>
                    <button @click="activeTab = 'attractions'" :class="tabClass('attractions')">Attractions</button>
                    <button @click="activeTab = 'map'"         :class="tabClass('map')">Map</button>
                    <button @click="activeTab = 'about'"       :class="tabClass('about')">About</button>
                    <button @click="activeTab = 'contact'"     :class="tabClass('contact')">Contact</button>
                    <button @click="activeTab = 'footer'"      :class="tabClass('footer')">Footer</button>
                </div>
            </div>
        </div>

        <div class="mt-5">

            <!-- ── HOME / HERO ─────────────────────────────────────────────── -->
            <div v-if="activeTab === 'home'">
                <div class="max-w-6xl mx-auto mt-5">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-5">

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-md font-semibold text-gray-700">Hero Section</p>
                            <p class="text-sm text-gray-500">Edit hero section on the landing page.</p>
                        </div>
                        <!-- Edit toggle -->
                        <button
                            type="button"
                            @click="heroEditing = !heroEditing"
                            class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl hover:bg-gray-900 hover:text-white transition">
                            {{ heroEditing ? 'Cancel Edit' : 'Edit' }}
                        </button>
                    </div>

                    <!-- Hero form -->
                    <form @submit.prevent="submitHero" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Tourism Tagline/Slogan</label>
                                <p class="text-sm text-gray-500 mb-2">e.g. Discover the beauty of</p>
                                <input
                                    v-model="heroForm.tagline"
                                    :disabled="!heroEditing"
                                    class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400"
                                    type="text"
                                />
                                <p v-if="heroForm.errors.tagline" class="text-red-500 text-xs mt-1">
                                    {{ heroForm.errors.tagline }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Barangay</label>
                                <p class="text-sm text-gray-500 mb-2">default: Bel-is</p>
                                <input
                                    v-model="heroForm.barangay"
                                    :disabled="!heroEditing"
                                    class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400"
                                    type="text"
                                />
                                <p v-if="heroForm.errors.barangay" class="text-red-500 text-xs mt-1">
                                    {{ heroForm.errors.barangay }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Municipality, Province, Country</label>
                                <p class="text-sm text-gray-500 mb-2">default: Buruanga, Aklan, Philippines</p>
                                <input
                                    v-model="heroForm.mun_prov"
                                    :disabled="!heroEditing"
                                    class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400"
                                    type="text"
                                />
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Subheadline</label>
                                <p class="text-sm text-gray-500 mb-2">e.g. Explore nature, culture, and hidden destinations</p>
                                <input
                                    v-model="heroForm.sub"
                                    :disabled="!heroEditing"
                                    class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400"
                                    type="text"
                                />
                            </div>
                        </div>

                        <!-- Background image upload -->
                        <div class="flex items-center gap-8 mt-5 bg-white rounded-lg p-4">
                            <div
                                @click="heroEditing && $refs.fileInput.click()"
                                :class="heroEditing ? 'cursor-pointer hover:bg-blue-50' : 'cursor-not-allowed opacity-60'"
                                class="relative flex flex-col items-center justify-center w-64 h-40 border-2 border-dashed border-blue-600 rounded-xl bg-blue-50/30 transition-colors"
                            >
                                <input
                                    type="file"
                                    ref="fileInput"
                                    class="hidden"
                                    accept="image/png, image/jpeg"
                                    @change="handleFileUpload"
                                />

                                <!-- Preview uploaded file -->
                                <template v-if="imagePreview">
                                    <img :src="imagePreview" class="w-full h-full object-cover rounded-xl" alt="Preview" />
                                </template>
                                <template v-else-if="hero.background_image_url">
                                    <img :src="hero.background_image_url" class="w-full h-full object-cover rounded-xl" alt="Current background" />
                                    <span class="absolute bottom-1 text-xs text-white bg-black/40 px-2 rounded">Current image</span>
                                </template>
                                <template v-else>
                                    <div class="mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-700">Upload your <span class="text-blue-600 font-semibold">image</span></p>
                                    <p class="text-sm text-gray-500">.png or .jpg are allowed only <br /> (Suggested: 3:1 Aspect Ratio)</p>
                                </template>
                            </div>

                            <div>
                                <h3 class="text-md font-bold text-gray-900 leading-tight">Background Image</h3>
                                <p class="text-gray-400 text-sm">Upload your new <br /> background image here.</p>
                                <p v-if="heroForm.errors.background_image" class="text-red-500 text-xs mt-1">
                                    {{ heroForm.errors.background_image }}
                                </p>
                            </div>
                        </div>

                        <!-- Save / Cancel buttons (only when editing) -->
                        <div v-if="heroEditing" class="flex justify-end mt-5 gap-5">
                            <button
                                type="submit"
                                :disabled="heroForm.processing"
                                class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 transition">
                                {{ heroForm.processing ? 'Saving…' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>

                </div>
                </div>
            </div>

            <!-- ── ATTRACTIONS ─────────────────────────────────────────────── -->
            <div v-else-if="activeTab === 'attractions'">
                <div class="max-w-6xl mx-auto mt-5">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-5">

                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <p class="text-md font-semibold text-gray-700">Attractions Section</p>
                            <p class="text-sm text-gray-500">Add, edit or remove attraction cards shown on the landing page.</p>
                        </div>
                        <button type="button" @click="openAddModal"
                            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-gray-700 transition">
                            + Add Attraction
                        </button>
                    </div>

                    <div v-if="props.attractions.length === 0" class="text-center py-16 text-gray-400">
                        No attractions yet. Click "+ Add Attraction" to get started.
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div v-for="attraction in pagedAttractions" :key="attraction.id"
                            class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                            <img :src="attraction.image_url || '/images/placeholder.jpg'"
                                class="w-full h-40 object-cover" :alt="attraction.name" />
                            <div class="p-3">
                                <h3 class="font-semibold text-gray-800">{{ attraction.name }}</h3>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ attraction.description }}</p>
                                <div class="flex gap-2 mt-3">
                                    <button @click="openEditModal(attraction)"
                                        class="flex-1 border border-blue-500 text-blue-500 text-xs font-bold py-1 rounded-lg hover:bg-blue-500 hover:text-white transition">
                                        Edit
                                    </button>
                                    <button @click="deleteAttraction(attraction.id)"
                                        class="flex-1 border border-red-400 text-red-400 text-xs font-bold py-1 rounded-lg hover:bg-red-400 hover:text-white transition">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="totalAttractionPages > 1" class="flex items-center justify-between mt-6">
                        <button @click="attractionPage = Math.max(1, attractionPage - 1)"
                            :disabled="attractionPage === 1"
                            class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-blue-600 hover:text-white transition disabled:opacity-40 disabled:cursor-not-allowed">
                            Back Page
                        </button>
                        <div class="flex gap-2">
                            <button v-for="p in totalAttractionPages" :key="p" @click="attractionPage = p"
                                :class="['px-3 py-1 border rounded text-sm transition',
                                    attractionPage === p
                                        ? 'bg-gray-900 text-white border-gray-900'
                                        : 'border-gray-300 text-gray-600 hover:bg-blue-600 hover:text-white']">
                                {{ p }}
                            </button>
                        </div>
                        <button @click="attractionPage = Math.min(totalAttractionPages, attractionPage + 1)"
                            :disabled="attractionPage === totalAttractionPages"
                            class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-blue-600 hover:text-white transition disabled:opacity-40 disabled:cursor-not-allowed">
                            Next Page
                        </button>
                    </div>

                </div>
                </div>

                <!-- Add / Edit Modal -->
                <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">
                            {{ editingAttraction ? 'Edit Attraction' : 'Add Attraction' }}
                        </h2>
                        <form @submit.prevent="submitAttraction" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Place Name</label>
                                <input v-model="attractionForm.name" type="text"
                                    placeholder="e.g. Bel-is Cove Beach Resort"
                                    class="border border-gray-300 rounded w-full py-2 px-3" />
                                <p v-if="attractionForm.errors.name" class="text-red-500 text-xs mt-1">
                                    {{ attractionForm.errors.name }}
                                </p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Short Description</label>
                                <textarea v-model="attractionForm.description" rows="3"
                                    placeholder="A short description of the place..."
                                    class="border border-gray-300 rounded w-full py-2 px-3 resize-none"></textarea>
                                <p v-if="attractionForm.errors.description" class="text-red-500 text-xs mt-1">
                                    {{ attractionForm.errors.description }}
                                </p>
                            </div>
                            <div class="mb-5">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Photo</label>
                                <div @click="$refs.attrFileInput.click()"
                                    class="relative flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-blue-500 rounded-xl bg-blue-50/30 cursor-pointer hover:bg-blue-50 transition">
                                    <input type="file" ref="attrFileInput" class="hidden"
                                        accept="image/png,image/jpeg" @change="handleAttractionImage" />
                                    <template v-if="attractionImagePreview">
                                        <img :src="attractionImagePreview" class="w-full h-full object-cover rounded-xl" />
                                    </template>
                                    <template v-else-if="editingAttraction?.image_url">
                                        <img :src="editingAttraction.image_url" class="w-full h-full object-cover rounded-xl" />
                                        <span class="absolute bottom-1 text-xs text-white bg-black/40 px-2 rounded">Current photo — click to replace</span>
                                    </template>
                                    <template v-else>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-blue-500 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <p class="text-sm text-gray-600">Click to upload <span class="text-gray-400">(.png or .jpg)</span></p>
                                    </template>
                                </div>
                                <p v-if="attractionForm.errors.image" class="text-red-500 text-xs mt-1">
                                    {{ attractionForm.errors.image }}
                                </p>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="closeModal"
                                    class="border border-gray-300 text-gray-600 text-sm font-bold px-5 py-2 rounded-xl hover:bg-gray-100 transition">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="attractionForm.processing"
                                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-700 transition">
                                    {{ attractionForm.processing ? 'Saving…' : (editingAttraction ? 'Save Changes' : 'Add Attraction') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div v-else-if="activeTab === 'map'">
                <!-- Map tab content -->
            </div>

            <div v-else-if="activeTab === 'about'">
                <!-- About tab content -->
            </div>

            <div v-else-if="activeTab === 'contact'">
                <div class="max-w-6xl mx-auto mt-5">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-5">

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-md font-semibold text-gray-700">Contact Information Section</p>
                            <p class="text-sm text-gray-500">Edit the contact information band on the landing page.</p>
                        </div>
                        <button
                            type="button"
                            @click="contactEditing = !contactEditing"
                            class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl hover:bg-gray-900 hover:text-white transition">
                            {{ contactEditing ? 'Cancel Edit' : 'Edit' }}
                        </button>
                    </div>

                    <form @submit.prevent="submitContact">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 p-4">

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Email Address</label>
                                <input
                                    v-model="contactForm.email"
                                    :disabled="!contactEditing"
                                    type="email"
                                    class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400"
                                />
                                <p v-if="contactForm.errors.email" class="text-red-500 text-xs mt-1">
                                    {{ contactForm.errors.email }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Phone Number</label>
                                <input
                                    v-model="contactForm.phone"
                                    :disabled="!contactEditing"
                                    type="text"
                                    class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400"
                                />
                                <p v-if="contactForm.errors.phone" class="text-red-500 text-xs mt-1">
                                    {{ contactForm.errors.phone }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Email Assistance Hours</label>
                                <p class="text-sm text-gray-500 mb-2">e.g. Monday – Friday 6 am to 8 pm</p>
                                <input
                                    v-model="contactForm.email_hours"
                                    :disabled="!contactEditing"
                                    type="text"
                                    class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400"
                                />
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Phone Assistance Hours</label>
                                <p class="text-sm text-gray-500 mb-2">e.g. Monday – Friday 6 am to 8 pm</p>
                                <input
                                    v-model="contactForm.phone_hours"
                                    :disabled="!contactEditing"
                                    type="text"
                                    class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400"
                                />
                            </div>

                        </div>

                        <div v-if="contactEditing" class="flex justify-end mt-5 gap-5">
                            <button
                                type="submit"
                                :disabled="contactForm.processing"
                                class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 transition">
                                {{ contactForm.processing ? 'Saving…' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>

                </div>
                </div>
            </div>

            <div v-else-if="activeTab === 'footer'">
                <!-- Footer tab content -->
            </div>

        </div>

    </LandingLayout>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

// ── Auto-dismiss flash banner ─────────────────────────────────────────────────
const page      = usePage()
const showFlash = ref(false)
let flashTimer  = null

watch(
    () => page.props.flash?.success,
    (msg) => {
        if (msg) {
            showFlash.value = true
            clearTimeout(flashTimer)
            flashTimer = setTimeout(() => { showFlash.value = false }, 4000)
        }
    },
    { immediate: true }
)

// ── Props from Inertia (passed by WebsiteContentController@index) ─────────────
const props = defineProps({
    hero: {
        type: Object,
        default: () => ({
            tagline: '',
            barangay: '',
            mun_prov: '',
            sub: '',
            background_image_url: null,
        }),
    },
    contact: {
        type: Object,
        default: () => ({
            email: '',
            phone: '',
            email_hours: '',
            phone_hours: '',
        }),
    },
    attractions: {
        type: Array,
        default: () => [],
    },
})

// ── Tab state ─────────────────────────────────────────────────────────────────
const activeTab   = ref('home')
const heroEditing = ref(false)

// ── Hero form (Inertia useForm for automatic error/processing handling) ────────
const heroForm = useForm({
    tagline:           props.hero.tagline,
    barangay:          props.hero.barangay,
    mun_prov:          props.hero.mun_prov,
    sub:               props.hero.sub,
    background_image:  null,   // File object when user picks one
})

// Sync form values whenever Inertia re-sends updated props (immediate: true populates on first load)
watch(
    () => props.hero,
    (newHero) => {
        heroForm.tagline  = newHero.tagline
        heroForm.barangay = newHero.barangay
        heroForm.mun_prov = newHero.mun_prov
        heroForm.sub      = newHero.sub
    },
    { immediate: true }
)

// Local image preview URL
const imagePreview = ref(null)

function handleFileUpload(event) {
    const file = event.target.files[0]
    if (!file) return
    heroForm.background_image = file
    imagePreview.value = URL.createObjectURL(file)
}

function submitHero() {
    heroForm.post(route('websitecontent.hero.update'), {
        forceFormData: true,   // needed because we may include a file
        onSuccess: () => {
            heroEditing.value = false
            imagePreview.value = null
        },
    })
}

// ── Contact form ──────────────────────────────────────────────────────────────
const contactEditing = ref(false)

const contactForm = useForm({
    email:       props.contact.email,
    phone:       props.contact.phone,
    email_hours: props.contact.email_hours,
    phone_hours: props.contact.phone_hours,
})

watch(
    () => props.contact,
    (c) => {
        contactForm.email       = c.email
        contactForm.phone       = c.phone
        contactForm.email_hours = c.email_hours
        contactForm.phone_hours = c.phone_hours
    },
    { immediate: true }
)

function submitContact() {
    contactForm.post(route('websitecontent.contact.update'), {
        onSuccess: () => { contactEditing.value = false },
    })
}

// ── Attractions ───────────────────────────────────────────────────────────────
const PER_PAGE           = 6
const showAddModal       = ref(false)
const editingAttraction  = ref(null)
const attractionPage     = ref(1)

const attractionForm = useForm({
    name:        '',
    description: '',
    image:       null,
})

const attractionImagePreview = ref(null)

const totalAttractionPages = computed(() =>
    Math.max(1, Math.ceil(props.attractions.length / PER_PAGE))
)

const pagedAttractions = computed(() => {
    const start = (attractionPage.value - 1) * PER_PAGE
    return props.attractions.slice(start, start + PER_PAGE)
})

watch(() => props.attractions, () => { attractionPage.value = 1 })

function openAddModal() {
    attractionForm.reset()
    attractionImagePreview.value = null
    editingAttraction.value = null
    showAddModal.value = true
}

function openEditModal(attraction) {
    attractionForm.name        = attraction.name
    attractionForm.description = attraction.description
    attractionForm.image       = null
    attractionImagePreview.value = null
    editingAttraction.value = attraction
    showAddModal.value = true
}

function closeModal() {
    showAddModal.value = false
    editingAttraction.value = null
    attractionForm.reset()
    attractionImagePreview.value = null
}

function handleAttractionImage(event) {
    const file = event.target.files[0]
    if (!file) return
    attractionForm.image = file
    attractionImagePreview.value = URL.createObjectURL(file)
}

function submitAttraction() {
    if (editingAttraction.value) {
        attractionForm.post(route('websitecontent.attractions.update', editingAttraction.value.id), {
            forceFormData: true,
            onSuccess: () => closeModal(),
        })
    } else {
        attractionForm.post(route('websitecontent.attractions.store'), {
            forceFormData: true,
            onSuccess: () => closeModal(),
        })
    }
}

function deleteAttraction(id) {
    if (!confirm('Delete this attraction?')) return
    useForm({}).delete(route('websitecontent.attractions.destroy', id))
}

// ── Nav helper ────────────────────────────────────────────────────────────────
const navClass = (routeName) => [
    'pb-2 text-sm font-semibold transition border-b-2',
    route().current(routeName)
        ? 'text-gray-900 border-gray-900'
        : 'text-gray-400 border-transparent hover:text-gray-600',
]

const tabClass = (tab) => [
    'px-4 py-2 border rounded-lg text-sm font-medium transition',
    activeTab.value === tab
        ? 'bg-gray-900 text-white border-gray-900'
        : 'text-gray-600 border-gray-300 hover:bg-gray-100',
]
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>