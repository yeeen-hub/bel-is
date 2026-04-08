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

            <Link
            :href="route('systemsettings')"
            :class="navClass('systemsettings')"
            >
            System Settings
            </Link>

            <Link
            :href="route('usermanagement')"
            :class="navClass('usermanagement')"
            >
            User Management
            </Link>

            <Link
            :href="route('auditlogs')"
            :class="navClass('auditlogs')"
            >
            Audit Logs
            </Link>

            <Link
            :href="route('websitecontent')"
            :class="navClass('websitecontent')"
            >
            Website Content
            </Link>

            <Link
            :href="route('virtualtour')"
            :class="navClass('virtualtour')"
            >
            Virtual Tour
            </Link>

            <Link
            :href="route('securitysettings')"
            :class="navClass('securitysettings')"
            >
            Security
            </Link>

        </div>

        <div class="flex items-center justify-between mt-5 p-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">Tourism Website</h1>

                    <div class="flex justify-center mt-2 gap-2">
                        <button @click="activeTab = 'home'" class="px-4 py-2 border rounded-lg">Home</button>
                        <button @click="activeTab = 'attractions'" class="px-4 py-2 border rounded-lg">Attractions</button>
                        <button @click="activeTab = 'map'" class="px-4 py-2 border rounded-lg">Map</button>
                        <button @click="activeTab = 'about'" class="px-4 py-2 border rounded-lg">About</button>
                        <button @click="activeTab = 'contact'" class="px-4 py-2 border rounded-lg">Contact</button>
                        <button @click="activeTab = 'footer'" class="px-4 py-2 border rounded-lg">Footer</button>
                    </div>
                </div>
                
        </div>

        <div class="mt-5">

            <div v-if="activeTab === 'home'">
                <div class="max-w-6xl mx-auto mt-5">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-5">
                    <div class=" flex items-center justify-between">
                    <div>
                    <p class="text-md font-semibold text-gray-700">
                        Hero Section
                    </p>
                    <p class="text-sm text-gray-500">
                        Edit hero section in your website.
                    </p>
                    </div>
                    <button
                    type="button"
                    class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-900 hover:text-white transition">
                    Edit
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold">First Phrase</label>
                            <p class="text-sm text-gray-500 mb-2">Discover the beauty of</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="text">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Second Phrase</label>
                            <p class="text-sm text-gray-500 mb-2">Bel-is</p>
                            <input class="border border-gray-400 rounded w-full py-2 px-3" type="text">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Third Phrase</label>
                            <p class="text-sm text-gray-500 mb-2">Located in Brgy. Buruanga, Aklan</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="text">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">4th Phrase</label>
                            <p class="text-sm text-gray-500 mb-2">Explore nature, culture and hidden destinations</p>
                            <input class="border border-gray-400 rounded w-full py-2 px-3" type="text">
                        </div>
                </div>

                <div class="flex items-center gap-8 mt-5 bg-white rounded-lg p-4">
                    <div 
                    @click="$refs.fileInput.click()"
                    class="relative flex flex-col items-center justify-center w-64 h-40 border-2 border-dashed border-blue-600 rounded-xl bg-blue-50/30 cursor-pointer hover:bg-blue-50 transition-colors"
                    >
                    <input 
                        type="file" 
                        ref="fileInput" 
                        class="hidden" 
                        accept="image/png, image/jpeg" 
                        @change="handleFileUpload"
                    />
                    
                    <div class="mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    
                    <p class="text-sm text-gray-700">
                        Upload your <span class="text-blue-600 font-semibold">image</span>
                    </p>
                    <p class="text-sm text-gray-500">.png or .jpg are allowed only</p>
                    </div>

                    <div>
                    <h3 class="text-md font-bold text-gray-900 leading-tight">
                        Background Image
                    </h3>
                    <p class="text-gray-400 text-sm">
                        Upload your new <br /> background image here.
                    </p>
                    </div>
                </div>
                
                <div class="flex justify-end mt-5 gap-5">
                <button
                    type="button"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Save Changes
                </button>

                <button
                    type="button"
                    class="border border-gray-700 text-gray-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Cancel
                </button>
                </div>
                </div>
                
                </div>
            </div>

            <div v-else-if="activeTab === 'attractions'">
                <div class="max-w-6xl mx-auto mt-5">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                    <div class=" flex items-center justify-between mb-5">
                    <div>
                    <p class="text-md font-semibold text-gray-700">
                        Featured Attractions
                    </p>
                    <p class="text-sm text-gray-500">
                        Edit featured attractions section in your website.
                    </p>
                    </div>

                    <div class="flex justify-end gap-5">
                        <button
                    type="button"
                    class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-900 hover:text-white transition">
                    Add New Attraction
                    </button>

                    <button
                    type="button"
                    class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-900 hover:text-white transition">
                    Edit
                    </button>
                    </div>
                    
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Name</label>
                            <p class="text-sm text-gray-500 mb-2">Tatay Ugoy's Resto</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="text">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Location</label>
                            <p class="text-sm text-gray-500 mb-2">Hinugtan,Bel-is</p>
                            <input class="border border-gray-400 rounded w-full py-2 px-3" type="text">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Description</label>
                            <p class="text-sm text-gray-500 mb-2">Located in Hinugtan, Bel-is... Read More</p>
                            <textarea class="border border-gray-00 rounded w-full py-2 px-3" rows="3"></textarea>
                        </div>

                </div>

                <div class="flex items-center gap-8 mt-5 bg-white rounded-lg p-4">
                    <div 
                    @click="$refs.fileInput.click()"
                    class="relative flex flex-col items-center justify-center w-64 h-40 border-2 border-dashed border-blue-600 rounded-xl bg-blue-50/30 cursor-pointer hover:bg-blue-50 transition-colors"
                    >
                    <input 
                        type="file" 
                        ref="fileInput" 
                        class="hidden" 
                        accept="image/png, image/jpeg" 
                        @change="handleFileUpload"
                    />
                    
                    <div class="mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    
                    <p class="text-sm text-gray-700">
                        Upload your <span class="text-blue-600 font-semibold">image</span>
                    </p>
                    <p class="text-sm text-gray-500">.png or .jpg are allowed only</p>
                    </div>

                    <div>
                    <h3 class="text-md font-bold text-gray-900 leading-tight">
                        Attraction Image
                    </h3>
                    <p class="text-gray-400 text-sm">
                        Upload your new <br /> attraction image here.
                    </p>
                    </div>
                </div>
                
                <div class="flex justify-end mt-5 gap-5">
                <button
                    type="button"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Save Changes
                </button>

                <button
                    type="button"
                    class="border border-gray-700 text-gray-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Cancel
                </button>
                </div>
                </div>
                
                </div>
            </div>

            <div v-else-if="activeTab === 'map'">
                <h1>Map Content</h1>
            </div>

            <div v-if="activeTab === 'about'">
                <div class="max-w-6xl mx-auto mt-5">
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-5">
                        <div class=" flex items-center justify-between">
                        <div>
                        <p class="text-md font-semibold text-gray-700">
                            About Bel-is
                        </p>
                        <p class="text-sm text-gray-500">
                            Manage the information displayed on the about page.
                        </p>
                        </div>
                        <button
                        type="button"
                        class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-900 hover:text-white transition">
                        Edit
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-2 p-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold">History</label>
                            <p class="text-sm text-gray-500 mb-2">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="text">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Growth of the Community</label>
                            <p class="text-sm text-gray-500 mb-2">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="text">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Bel-is Today</label>
                            <p class="text-sm text-gray-500 mb-2">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="text">
                        </div>

                    </div>

                    <div class="flex justify-end mt-5 gap-5">
                <button
                    type="button"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Save Changes
                </button>

                <button
                    type="button"
                    class="border border-gray-700 text-gray-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Cancel
                </button>
                </div>
                </div>
            </div>
            </div>

            <div v-else-if="activeTab === 'contact'">
                <div class="max-w-6xl mx-auto mt-5">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-5">
                    <div class=" flex items-center justify-between">
                    <div>
                    <p class="text-md font-semibold text-gray-700">
                        Contact Page Content
                    </p>
                    <p class="text-sm text-gray-500">
                        Manage contact information and form details.
                    </p>
                    </div>
                    <button
                    type="button"
                    class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-900 hover:text-white transition">
                    Edit
                    </button>
                </div>

                <div class="p-4">
                    <p class="text-md font-bold text-gray-900 leading-tight">
                        Contact Information
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Email Address</label>
                                <p class="text-sm text-gray-500 mb-2">help@bel-is.com</p>
                                <input class="border border-gray-00 rounded w-full py-2 px-3" type="email">
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Availability</label>
                                <p class="text-sm text-gray-500 mb-2">Monday - Friday: 9:00 AM - 5:00 PM</p>
                                <input class="border border-gray-400 rounded w-full py-2 px-3" type="text">
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Phone Number</label>
                                <p class="text-sm text-gray-500 mb-2">+63 912 345 6789</p>
                                <input class="border border-gray-00 rounded w-full py-2 px-3" type="number">
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Availability</label>
                                <p class="text-sm text-gray-500 mb-2">Monday - Friday: 9:00 AM - 5:00 PM</p>
                                <input class="border border-gray-400 rounded w-full py-2 px-3" type="text">
                            </div>
                    </div>
                </div>

                <div class="p-4">
                    <p class="text-md font-bold text-gray-900 leading-tight">
                        Social Links
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Facebook Link</label>
                            <p class="text-sm text-gray-500 mb-2">https://www.facebook.com/bel-is</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="url">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Instagram Link</label>
                            <p class="text-sm text-gray-500 mb-2">https://www.instagram.com/bel-is</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="url">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Twitter Link</label>
                            <p class="text-sm text-gray-500 mb-2">https://twitter.com/bel_is</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="url">
                        </div>

                    </div>
                </div>
                
                <div class="flex justify-end mt-5 gap-5">
                <button
                    type="button"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Save Changes
                </button>

                <button
                    type="button"
                    class="border border-gray-700 text-gray-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Cancel
                </button>
                </div>
                </div>
                
                </div>
            </div>

            <div v-else-if="activeTab === 'footer'">
                <div class="max-w-6xl mx-auto mt-5">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 mb-5">
                    <div class=" flex items-center justify-between">
                    <div>
                    <p class="text-md font-semibold text-gray-700">
                        Footer Content
                    </p>
                    <p class="text-sm text-gray-500">
                        Manage footer information and links.
                    </p>
                    </div>
                    <button
                    type="button"
                    class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-900 hover:text-white transition">
                    Edit
                    </button>
                </div>

                <div class="p-4">
                    <p class="text-md font-bold text-gray-900 leading-tight">
                        Social Links
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Facebook Link</label>
                            <p class="text-sm text-gray-500 mb-2">https://www.facebook.com/bel-is</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="url">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">Instagram Link</label>
                            <p class="text-sm text-gray-500 mb-2">https://www.instagram.com/bel-is</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="url">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold">LinkedIn Link</label>
                            <p class="text-sm text-gray-500 mb-2">https://www.linkedin.com/company/bel-is</p>
                            <input class="border border-gray-00 rounded w-full py-2 px-3" type="url">
                        </div>

                    </div>
                </div>
                
                <div class="flex justify-end mt-5 gap-5">
                <button
                    type="button"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Save Changes
                </button>

                <button
                    type="button"
                    class="border border-gray-700 text-gray-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Cancel
                </button>
                </div>
                </div>
                
                </div>
            </div>

        </div> 
        
        
        
    </LandingLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref } from 'vue'

const activeTab = ref('home')
import LandingLayout from '@/Layouts/SidebarLayout.vue';

const navClass = (routeName) => [
  'pb-2 text-sm font-semibold transition border-b-2',
  route().current(routeName)
    ? 'text-gray-900 border-gray-900'
    : 'text-gray-400 border-transparent hover:text-gray-600'
]
</script>