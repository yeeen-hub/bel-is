<template>
    <LandingLayout>
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">

                <div class="relative flex-1">
                    <input v-model="search" type="text" placeholder="Search by name, origin, or registration ID..."
                        :class="[
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

                <FontAwesomeIcon icon="user" class="text-gray-700" />
            </div>
        </div>

        <div class="p-4 mt-4 rounded-lg flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">System Setting</h1>
                    <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
                </div>
            </div>
        </div>

        <div
            class="border-b border-gray-300 flex flex-wrap justify-start sm:justify-center gap-3 sm:gap-6 px-3 sm:px-0 overflow-x-auto whitespace-nowrap">

            <Link v-if="can('view_system_settings')" :href="route('settings')" :class="navClass('settings')"
                class="text-sm sm:text-base">
                General Settings
            </Link>

            <Link v-if="can('view_user_management')" :href="route('usermanagement')" :class="navClass('usermanagement')"
                class="text-sm sm:text-base">
                User Management
            </Link>

            <Link v-if="can('view_audit_logs')" :href="route('auditlogs')" :class="navClass('auditlogs')"
                class="text-sm sm:text-base">
                Audit Logs
            </Link>

            <Link v-if="can('view_website_content')" :href="route('websitecontent')" :class="navClass('websitecontent')"
                class="text-sm sm:text-base">
                Website Content
            </Link>

            <Link v-if="can('view_virtual_tour')" :href="route('virtualtour')" :class="navClass('virtualtour')"
                class="text-sm sm:text-base">
                Virtual Tour
            </Link>

            <Link v-if="can('view_security')" :href="route('securitysettings')" :class="navClass('securitysettings')"
                class="text-sm sm:text-base">
                Security
            </Link>

        </div>

        <!-- Success flash message — auto-dismisses after 4 seconds -->
        <transition name="fade">
            <div v-if="showFlash"
                class="max-w-6xl mx-auto mt-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm flex items-center justify-between">
                <span>{{ $page.props.flash?.success }}</span>
                <button @click="showFlash = false"
                    class="ml-4 text-green-600 hover:text-green-900 font-bold text-lg leading-none">&times;</button>
            </div>
        </transition>

        <div class="flex flex-col items-center justify-between mt-5 p-4 gap-3">

            <div class="w-full">

                <div class="flex flex-wrap justify-center gap-2">

                    <button @click="activeTab = 'home'" :class="tabClass('home')"
                        class="px-3 py-1.5 text-sm rounded-md whitespace-nowrap">
                        Home
                    </button>

                    <button @click="activeTab = 'attractions'" :class="tabClass('attractions')"
                        class="px-3 py-1.5 text-sm rounded-md whitespace-nowrap">
                        Attractions
                    </button>

                    <button @click="activeTab = 'map'" :class="tabClass('map')"
                        class="px-3 py-1.5 text-sm rounded-md whitespace-nowrap">
                        Map
                    </button>

                    <button @click="activeTab = 'about'" :class="tabClass('about')"
                        class="px-3 py-1.5 text-sm rounded-md whitespace-nowrap">
                        About
                    </button>

                    <button @click="activeTab = 'contact'" :class="tabClass('contact')"
                        class="px-3 py-1.5 text-sm rounded-md whitespace-nowrap">
                        Contact
                    </button>

                    <button @click="activeTab = 'footer'" :class="tabClass('footer')"
                        class="px-3 py-1.5 text-sm rounded-md whitespace-nowrap">
                        Footer
                    </button>

                </div>

            </div>

        </div>

        <div class="mt-5">

            <!-- ── HOME / HERO ─────────────────────────────────────────────── -->
            <div v-if="activeTab === 'home'">

                <div class="max-w-6xl mx-auto mt-5 px-4">

                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-5">

                        <!-- HEADER -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                            <div>
                                <p class="text-md font-semibold text-gray-700">Hero Section</p>
                                <p class="text-sm text-gray-500">
                                    Edit hero section on the landing page.
                                </p>
                            </div>

                            <button type="button" @click="heroEditing = !heroEditing"
                                class="w-full sm:w-auto border border-blue-500 text-blue-500 text-sm font-bold px-4 py-2 rounded-xl hover:bg-gray-900 hover:text-white transition">
                                {{ heroEditing ? 'Cancel Edit' : 'Edit' }}
                            </button>

                        </div>

                        <!-- FORM -->
                        <form @submit.prevent="submitHero" enctype="multipart/form-data">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">

                                <div>
                                    <label class="text-sm font-bold text-gray-700">Tourism Tagline</label>
                                    <input v-model="heroForm.tagline" :disabled="!heroEditing"
                                        class="w-full border rounded px-3 py-2 disabled:bg-gray-50 text-sm"
                                        type="text" />
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-gray-700">Barangay</label>
                                    <input v-model="heroForm.barangay" :disabled="!heroEditing"
                                        class="w-full border rounded px-3 py-2 disabled:bg-gray-50 text-sm"
                                        type="text" />
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-gray-700">Municipality / Province</label>
                                    <input v-model="heroForm.mun_prov" :disabled="!heroEditing"
                                        class="w-full border rounded px-3 py-2 disabled:bg-gray-50 text-sm"
                                        type="text" />
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-gray-700">Subheadline</label>
                                    <input v-model="heroForm.sub" :disabled="!heroEditing"
                                        class="w-full border rounded px-3 py-2 disabled:bg-gray-50 text-sm"
                                        type="text" />
                                </div>

                            </div>

                            <!-- IMAGE UPLOAD -->
                            <div class="mt-5 flex flex-col lg:flex-row gap-5">

                                <!-- Upload box -->
                                <div class="w-full lg:w-64">

                                    <div @click="heroEditing && $refs.fileInput.click()"
                                        :class="heroEditing ? 'cursor-pointer' : 'cursor-not-allowed opacity-60'"
                                        class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-blue-600 rounded-xl bg-blue-50/30">

                                        <input type="file" ref="fileInput" class="hidden" accept="image/png, image/jpeg"
                                            @change="handleFileUpload" />

                                        <template v-if="imagePreview">
                                            <img :src="imagePreview" class="w-full h-full object-cover rounded-xl" />
                                        </template>

                                        <template v-else-if="hero.background_image_url">
                                            <img :src="hero.background_image_url"
                                                class="w-full h-full object-cover rounded-xl" />
                                        </template>

                                        <template v-else>
                                            <p class="text-sm text-gray-600 text-center">
                                                Upload Image
                                            </p>
                                            <p class="text-xs text-gray-400 text-center">
                                                PNG / JPG only
                                            </p>
                                        </template>

                                    </div>

                                </div>

                                <!-- Text info -->
                                <div class="flex-1">
                                    <h3 class="text-md font-bold">Background Image</h3>
                                    <p class="text-sm text-gray-400">
                                        Upload a new background image for hero section.
                                    </p>
                                </div>

                            </div>

                            <!-- SAVE BUTTON -->
                            <div v-if="heroEditing" class="flex justify-end mt-5">

                                <button type="submit"
                                    class="w-full sm:w-auto bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl hover:bg-gray-800">
                                    Save Changes
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

            <!-- ── ATTRACTIONS ─────────────────────────────────────────────── -->
            <div v-else-if="activeTab === 'attractions'">
                <div class="max-w-6xl mx-auto mt-5 px-4 sm:px-6">

                    <!-- Header -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-5">

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                            <div>
                                <p class="text-md font-semibold text-gray-700">Attractions Section</p>
                                <p class="text-sm text-gray-500">
                                    Add, edit or remove attraction cards shown on the landing page.
                                </p>
                            </div>

                            <button type="button" @click="openAddModal"
                                class="w-full sm:w-auto bg-gray-900 text-white text-sm font-bold px-4 sm:px-5 py-2 rounded-xl hover:bg-gray-700 transition">
                                + Add Attraction
                            </button>
                        </div>

                        <!-- Empty state -->
                        <div v-if="props.attractions.length === 0" class="text-center py-16 text-gray-400">
                            No attractions yet. Click "+ Add Attraction" to get started.
                        </div>

                        <!-- Cards -->
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">

                            <div v-for="attraction in pagedAttractions" :key="attraction.id"
                                class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                <img :src="attraction.image_url || '/images/placeholder.jpg'"
                                    class="w-full h-36 sm:h-40 object-cover" />

                                <div class="p-3 sm:p-4">
                                    <h3 class="font-semibold text-gray-800 text-sm sm:text-base">
                                        {{ attraction.name }}
                                    </h3>

                                    <h3 class="text-gray-600 text-xs sm:text-sm">
                                        {{ attraction.location }}
                                    </h3>

                                    <p class="text-xs sm:text-sm text-gray-500 mt-1 line-clamp-2">
                                        {{ attraction.description }}
                                    </p>

                                    <div class="flex flex-col sm:flex-row gap-2 mt-3">
                                        <button @click="openEditModal(attraction)"
                                            class="w-full border border-blue-500 text-blue-500 text-xs font-bold py-1 rounded-lg hover:bg-blue-500 hover:text-white transition">
                                            Edit
                                        </button>

                                        <button @click="deleteAttraction(attraction.id)"
                                            class="w-full border border-red-400 text-red-400 text-xs font-bold py-1 rounded-lg hover:bg-red-400 hover:text-white transition">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Pagination -->
                        <div v-if="totalAttractionPages > 1"
                            class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-6">
                            <button @click="attractionPage = Math.max(1, attractionPage - 1)"
                                :disabled="attractionPage === 1"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 text-gray-600 rounded-lg disabled:opacity-40">
                                Back
                            </button>

                            <div class="flex flex-wrap justify-center gap-2">
                                <button v-for="p in totalAttractionPages" :key="p" @click="attractionPage = p" :class="[
                                    'px-3 py-1 border rounded text-sm',
                                    attractionPage === p
                                        ? 'bg-gray-900 text-white border-gray-900'
                                        : 'border-gray-300 text-gray-600'
                                ]">
                                    {{ p }}
                                </button>
                            </div>

                            <button @click="attractionPage = Math.min(totalAttractionPages, attractionPage + 1)"
                                :disabled="attractionPage === totalAttractionPages"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 text-gray-600 rounded-lg disabled:opacity-40">
                                Next
                            </button>
                        </div>

                    </div>
                </div>

                <!-- MODAL -->
                <div v-if="showAddModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-3 sm:p-6">

                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-4 sm:p-6">

                        <h2 class="text-lg font-semibold text-gray-800 mb-4">
                            {{ editingAttraction ? 'Edit Attraction' : 'Add Attraction' }}
                        </h2>

                        <form @submit.prevent="submitAttraction">

                            <div class="space-y-3">

                                <input v-model="attractionForm.name" type="text" placeholder="Place Name"
                                    class="w-full border border-gray-300 rounded p-2 text-sm" />

                                <input v-model="attractionForm.location" type="text" placeholder="Location"
                                    class="w-full border border-gray-300 rounded p-2 text-sm" />

                                <textarea v-model="attractionForm.description" rows="3" placeholder="Description"
                                    class="w-full border border-gray-300 rounded p-2 text-sm"></textarea>

                                <!-- Upload -->
                                <div @click="$refs.attrFileInput.click()"
                                    class="border-2 border-dashed border-blue-500 rounded-xl p-4 text-center cursor-pointer">
                                    <input type="file" ref="attrFileInput" class="hidden" />

                                    <p class="text-sm text-gray-500">
                                        Click to upload image
                                    </p>
                                </div>

                            </div>

                            <!-- Buttons -->
                            <div class="flex flex-col sm:flex-row justify-end gap-2 mt-5">

                                <button type="button" @click="closeModal"
                                    class="w-full sm:w-auto border border-gray-300 px-4 py-2 rounded-lg text-sm">
                                    Cancel
                                </button>

                                <button type="submit"
                                    class="w-full sm:w-auto bg-gray-900 text-white px-4 py-2 rounded-lg text-sm">
                                    Save
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
                <div class="max-w-6xl mx-auto mt-5 space-y-5 px-4 sm:px-6">

                    <!-- ABOUT TEXT SECTION -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5">

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                            <div>
                                <p class="text-md font-semibold text-gray-700">About Section</p>
                                <p class="text-sm text-gray-500">
                                    Edit the title, subtitle and three feature points.
                                </p>
                            </div>

                            <button type="button" @click="aboutEditing = !aboutEditing"
                                class="w-full sm:w-auto border border-blue-500 text-blue-500 text-sm font-bold px-4 sm:px-5 py-2 rounded-xl hover:bg-gray-900 hover:text-white transition">
                                {{ aboutEditing ? 'Cancel Edit' : 'Edit' }}
                            </button>
                        </div>

                        <form @submit.prevent="submitAbout">

                            <!-- Title/Sub -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-1 sm:p-2">

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Subtitle</label>
                                    <input v-model="aboutForm.subtitle" :disabled="!aboutEditing"
                                        class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 text-sm" />
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Title</label>
                                    <input v-model="aboutForm.title" :disabled="!aboutEditing"
                                        class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 text-sm" />
                                </div>

                            </div>

                            <!-- FEATURES -->
                            <div class="mt-4 space-y-4 p-1 sm:p-2">

                                <p class="text-sm font-bold text-gray-600 uppercase tracking-wide">
                                    Feature Points
                                </p>

                                <div v-for="n in [1, 2, 3]" :key="n"
                                    class="grid grid-cols-1 sm:grid-cols-3 gap-3 border border-gray-100 rounded-xl p-3 bg-gray-50">

                                    <div class="flex items-center gap-3 sm:gap-2">
                                        <span class="text-xl sm:text-2xl font-bold text-gray-300">
                                            0{{ n }}
                                        </span>

                                        <div class="flex-1">
                                            <label class="block text-xs text-gray-500 mb-1">Title</label>
                                            <input v-model="aboutForm[`feature${n}_title`]" :disabled="!aboutEditing"
                                                class="border border-gray-300 rounded w-full py-2 px-2 text-sm disabled:bg-white" />
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">Description</label>
                                        <textarea v-model="aboutForm[`feature${n}_desc`]" :disabled="!aboutEditing"
                                            rows="2"
                                            class="border border-gray-300 rounded w-full py-2 px-2 text-sm resize-none disabled:bg-white"></textarea>
                                    </div>

                                </div>

                            </div>

                            <!-- BUTTONS -->
                            <div v-if="aboutEditing" class="flex flex-col sm:flex-row justify-end mt-5 gap-3">
                                <button type="submit"
                                    class="w-full sm:w-auto bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl">
                                    Save Changes
                                </button>

                                <button type="button" @click="aboutEditing = false"
                                    class="w-full sm:w-auto border border-gray-300 text-gray-600 text-sm font-bold px-5 py-2 rounded-xl">
                                    Cancel
                                </button>
                            </div>

                        </form>
                    </div>

                    <!-- SLIDESHOW SECTION -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5">

                        <p class="text-md font-semibold text-gray-700 mb-1">Slideshow Images</p>
                        <p class="text-sm text-gray-500 mb-4">
                            These images auto-switch in the About section.
                        </p>

                        <!-- Upload -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-5">

                            <div @click="$refs.aboutImgInput.click()"
                                class="relative flex flex-col items-center justify-center w-full sm:w-40 h-28 border-2 border-dashed border-blue-500 rounded-xl bg-blue-50/30 cursor-pointer">
                                <input type="file" ref="aboutImgInput" class="hidden" />

                                <template v-if="aboutImagePreview">
                                    <img :src="aboutImagePreview" class="w-full h-full object-cover rounded-xl" />
                                </template>

                                <template v-else>
                                    <p class="text-xs text-gray-500">Click to pick image</p>
                                </template>

                            </div>

                            <button type="button" @click="uploadAboutImage"
                                class="w-full sm:w-auto bg-gray-900 text-white text-sm font-bold px-4 py-2 rounded-xl">
                                Upload Image
                            </button>

                        </div>

                        <!-- Images -->
                        <div v-if="props.about_images.length === 0" class="text-center py-8 text-gray-400 text-sm">
                            No images yet. Upload some above.
                        </div>

                        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            <div v-for="img in props.about_images" :key="img.id"
                                class="relative rounded-xl overflow-hidden group h-24 sm:h-28">
                                <img :src="img.image_url" class="w-full h-full object-cover" />

                                <button @click="deleteAboutImage(img.id)"
                                    class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 text-white text-xs font-bold flex items-center justify-center">
                                    Remove
                                </button>

                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <div v-else-if="activeTab === 'contact'">
                <div class="max-w-6xl mx-auto mt-5 space-y-5">

                    <!-- Contact Info Card -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                        <div class="flex items-center justify-between mb-1">
                            <div>
                                <p class="text-md font-semibold text-gray-700">Contact Information</p>
                                <p class="text-sm text-gray-500">Edit the contact info band on the landing page.</p>
                            </div>
                            <button type="button" @click="contactEditing = !contactEditing"
                                class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl hover:bg-gray-900 hover:text-white transition">
                                {{ contactEditing ? 'Cancel Edit' : 'Edit' }}
                            </button>
                        </div>

                        <form @submit.prevent="submitContact">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 p-2">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-1">Email Address</label>
                                    <input v-model="contactForm.email" :disabled="!contactEditing" type="email"
                                        class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400" />
                                    <p v-if="contactForm.errors.email" class="text-red-500 text-xs mt-1">{{ contactForm.errors.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-1">Phone Number</label>
                                    <input v-model="contactForm.phone" :disabled="!contactEditing" type="text"
                                        class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400" />
                                    <p v-if="contactForm.errors.phone" class="text-red-500 text-xs mt-1">{{ contactForm.errors.phone }}</p>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-1">Email Assistance Hours</label>
                                    <p class="text-xs text-gray-500 mb-1">e.g. Monday – Friday 6 am to 8 pm</p>
                                    <input v-model="contactForm.email_hours" :disabled="!contactEditing" type="text"
                                        class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400" />
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-1">Phone Assistance Hours</label>
                                    <p class="text-xs text-gray-500 mb-1">e.g. Monday – Friday 6 am to 8 pm</p>
                                    <input v-model="contactForm.phone_hours" :disabled="!contactEditing" type="text"
                                        class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400" />
                                </div>
                            </div>

                            <!-- Social Links -->
                            <div class="px-2 pt-2 pb-1">
                                <p class="text-sm font-bold text-gray-600 uppercase tracking-wide mb-3">Social Links</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-1">Facebook URL</label>
                                        <input v-model="contactForm.facebook_url" :disabled="!contactEditing" type="url"
                                            placeholder="https://facebook.com/..."
                                            class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400" />
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-1">Instagram URL</label>
                                        <input v-model="contactForm.instagram_url" :disabled="!contactEditing" type="url"
                                            placeholder="https://instagram.com/..."
                                            class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400" />
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-bold mb-1">X / Twitter URL</label>
                                        <input v-model="contactForm.twitter_url" :disabled="!contactEditing" type="url"
                                            placeholder="https://x.com/..."
                                            class="border border-gray-300 rounded w-full py-2 px-3 disabled:bg-gray-50 disabled:text-gray-400" />
                                    </div>
                                </div>
                            </div>

                            <div v-if="contactEditing" class="flex justify-end mt-4 gap-3 px-2">
                                <button type="submit" :disabled="contactForm.processing"
                                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 transition">
                                    {{ contactForm.processing ? 'Saving…' : 'Save Changes' }}
                                </button>
                                <button type="button" @click="contactEditing = false"
                                    class="border border-gray-300 text-gray-600 text-sm font-bold px-5 py-2 rounded-xl hover:bg-gray-100 transition">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Messages Inbox Card -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-md font-semibold text-gray-700">Messages Inbox</p>
                            <span v-if="unread_count > 0"
                                class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ unread_count }} new
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">Messages sent by visitors from the landing page.</p>

                        <div v-if="messages.length === 0" class="text-center py-10 text-gray-400 text-sm">
                            No messages yet.
                        </div>

                        <div v-else class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
                            <div v-for="msg in messages" :key="msg.id"
                                :class="['border rounded-xl p-4 transition', msg.is_read ? 'border-gray-200 bg-white' : 'border-blue-200 bg-blue-50']">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-semibold text-gray-800 text-sm">{{ msg.name }}</p>
                                            <span v-if="!msg.is_read" class="text-xs bg-blue-500 text-white px-2 py-0.5 rounded-full">New</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ msg.email }}{{ msg.phone ? ' · ' + msg.phone : '' }}
                                        </p>
                                        <p class="text-sm text-gray-700 mt-2 leading-relaxed">{{ msg.message }}</p>
                                        <p class="text-xs text-gray-400 mt-2">{{ msg.created_at }}</p>
                                    </div>
                                    <div class="flex flex-col gap-2 flex-shrink-0">
                                        <button v-if="!msg.is_read"
                                            @click="useForm({}).patch(route('messages.read', msg.id))"
                                            class="text-xs border border-gray-300 text-gray-600 px-3 py-1 rounded-lg hover:bg-gray-100 transition whitespace-nowrap">
                                            Mark Read
                                        </button>
                                        <button @click="deleteMsg(msg.id)"
                                            class="text-xs border border-red-300 text-red-500 px-3 py-1 rounded-lg hover:bg-red-50 transition">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
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

const page = usePage();

const permissions = computed(() => page.props.auth?.permissions ?? []);
const userRole = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase());

const can = (permission) => {
    if (userRole.value === 'admin') return true;
    return permissions.value.includes(permission);
};

// ── Auto-dismiss flash banner ─────────────────────────────────────────────────
const showFlash = ref(false)
let flashTimer = null

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
            facebook_url: '',
            instagram_url: '',
            twitter_url: '',
        }),
    },
    attractions: {
        type: Array,
        default: () => [],
    },
    about: {
        type: Object,
        default: () => ({
            title: '', subtitle: '',
            feature1_title: '', feature1_desc: '',
            feature2_title: '', feature2_desc: '',
            feature3_title: '', feature3_desc: '',
        }),
    },
    about_images: {
        type: Array,
        default: () => [],
    },
    messages: {
        type: Array,
        default: () => [],
    },
    unread_count: {
        type: Number,
        default: 0,
    },
})

// ── Tab state ─────────────────────────────────────────────────────────────────
const activeTab = ref('home')
const heroEditing = ref(false)

// ── Hero form (Inertia useForm for automatic error/processing handling) ────────
const heroForm = useForm({
    tagline: props.hero.tagline,
    barangay: props.hero.barangay,
    mun_prov: props.hero.mun_prov,
    sub: props.hero.sub,
    background_image: null,   // File object when user picks one
})

// Sync form values whenever Inertia re-sends updated props (immediate: true populates on first load)
watch(
    () => props.hero,
    (newHero) => {
        heroForm.tagline = newHero.tagline
        heroForm.barangay = newHero.barangay
        heroForm.mun_prov = newHero.mun_prov
        heroForm.sub = newHero.sub
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
    email:         props.contact.email,
    phone:         props.contact.phone,
    email_hours:   props.contact.email_hours,
    phone_hours:   props.contact.phone_hours,
    facebook_url:  props.contact.facebook_url  ?? '',
    instagram_url: props.contact.instagram_url ?? '',
    twitter_url:   props.contact.twitter_url   ?? '',
})

watch(
    () => props.contact,
    (c) => {
        contactForm.email         = c.email
        contactForm.phone         = c.phone
        contactForm.email_hours   = c.email_hours
        contactForm.phone_hours   = c.phone_hours
        contactForm.facebook_url  = c.facebook_url  ?? ''
        contactForm.instagram_url = c.instagram_url ?? ''
        contactForm.twitter_url   = c.twitter_url   ?? ''
    },
    { immediate: true }
)

function submitContact() {
    contactForm.post(route('websitecontent.contact.update'), {
        onSuccess: () => { contactEditing.value = false },
    })
}

function deleteMsg(id) {
    if (!confirm('Delete this message?')) return
    useForm({}).delete(route('messages.delete', id))
}

// ── Attractions ───────────────────────────────────────────────────────────────
const PER_PAGE = 6
const showAddModal = ref(false)
const editingAttraction = ref(null)
const attractionPage = ref(1)

const attractionForm = useForm({
    name: '',
    description: '',
    location: '',
    image: null,
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
    attractionForm.name = attraction.name
    attractionForm.description = attraction.description
    attractionForm.location = attraction.location
    attractionForm.image = null
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

// ── About ─────────────────────────────────────────────────────────────────────
const aboutEditing = ref(false)

const aboutForm = useForm({
    title: props.about.title,
    subtitle: props.about.subtitle,
    feature1_title: props.about.feature1_title,
    feature1_desc: props.about.feature1_desc,
    feature2_title: props.about.feature2_title,
    feature2_desc: props.about.feature2_desc,
    feature3_title: props.about.feature3_title,
    feature3_desc: props.about.feature3_desc,
})

watch(() => props.about, (a) => {
    aboutForm.title = a.title
    aboutForm.subtitle = a.subtitle
    aboutForm.feature1_title = a.feature1_title
    aboutForm.feature1_desc = a.feature1_desc
    aboutForm.feature2_title = a.feature2_title
    aboutForm.feature2_desc = a.feature2_desc
    aboutForm.feature3_title = a.feature3_title
    aboutForm.feature3_desc = a.feature3_desc
}, { immediate: true })

function submitAbout() {
    aboutForm.post(route('websitecontent.about.update'), {
        onSuccess: () => { aboutEditing.value = false },
    })
}

// About images
const aboutImageForm = useForm({ image: null })
const aboutImagePreview = ref(null)

function handleAboutImage(event) {
    const file = event.target.files[0]
    if (!file) return
    aboutImageForm.image = file
    aboutImagePreview.value = URL.createObjectURL(file)
}

function uploadAboutImage() {
    aboutImageForm.post(route('websitecontent.about.images.store'), {
        forceFormData: true,
        onSuccess: () => {
            aboutImageForm.reset()
            aboutImagePreview.value = null
        },
    })
}

function deleteAboutImage(id) {
    if (!confirm('Remove this image?')) return
    useForm({}).delete(route('websitecontent.about.images.destroy', id))
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
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>