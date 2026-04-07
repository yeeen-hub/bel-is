<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import PrimaryButton from '@/Components/PrimaryButton.vue';

const logoutForm = useForm({})
const logout = () => logoutForm.post(route('logout'))

import { ref } from 'vue'

const showReports = ref(false)
</script>

<template>
<div class="min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 p-6 bg-gray-100 flex flex-col">

        <div class="flex items-center space-x-4 mb-4">
            <Link :href="route('login')">
                <img src="/images/brgylogo.png" class="h-14 w-14 rounded-full" />
            </Link>

            <div>
                <h1 class="font-heading text-xl">BEL-IS</h1>
                <span class="text-gray-400 text-sm">System</span>
            </div>
        </div>

        <hr class="border-black mb-4" />

        <nav class="flex flex-col space-y-3 text-gray-600 text-base">

            <Link href="/admindb" class="flex items-center space-x-2 hover:bg-gray-200 p-2 rounded-lg">
                <FontAwesomeIcon icon="gauge" />
                <span class="font-semibold">Dashboard</span>
            </Link>

            <Link :href="route('registration')" class="flex items-center space-x-2 hover:bg-gray-200 p-2 rounded-lg">
                <FontAwesomeIcon icon="user-plus" />
                <span class="font-semibold">Registration</span>
            </Link>

            <Link :href="route('visitor-records')" class="flex items-center space-x-2 hover:bg-gray-200 p-2 rounded-lg">
                <FontAwesomeIcon icon="users" />
                <span class="font-semibold">Visitor Records</span>
            </Link>

            <div class="flex flex-col">

                <!-- Reports Button (NO Link here) -->
                <button 
                    @click="showReports = !showReports"
                    class="w-full flex items-center justify-between hover:bg-gray-200 p-2 rounded-lg"
                >
                    <div class="flex items-center space-x-2">
                        <FontAwesomeIcon icon="chart-bar" />
                        <span class="font-semibold">Reports</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': showReports }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>

                <!-- Dropdown -->
                <div v-if="showReports" class="ml-8 mt-2 flex flex-col gap-2">

                    <Link :href="route('reports')"
                        class="font-semibold border-2 border-gray-200 hover:bg-gray-200 p-2 rounded-lg text-sm">
                        Analytics
                    </Link>

                    <Link :href="route('feerevenue')"
                        class="font-semibold border-2 border-gray-200 hover:bg-gray-200 p-2 rounded-lg text-sm">
                        Fee Revenue
                    </Link>

                </div>
            </div>

            <Link :href="route('settings')" class="flex items-center space-x-2 hover:bg-gray-200 p-2 rounded-lg">
                <FontAwesomeIcon icon="cog" />
                <span class="font-semibold">Settings</span>
            </Link>

        </nav>

        <PrimaryButton 
            @click="logout"
            :disabled="logoutForm.processing"
            class="mt-auto bg-gray-800 text-white hover:bg-gray-900 w-full"
        >
            {{ logoutForm.processing ? 'Logging out...' : 'Logout' }}
        </PrimaryButton>

    </aside>


    <!-- Page Content -->
    <main class="flex-1 p-8 bg-gray-50">
        <slot />
    </main>

</div>
</template>