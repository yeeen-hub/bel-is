<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { ref, computed } from 'vue'

const logoutForm = useForm({})
const logout = () => logoutForm.post(route('logout'))
const showReports = ref(false)

const page        = usePage()
const permissions = computed(() => page.props.auth?.permissions ?? [])
const userRole    = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase())

const can = (permission) => {
    if (userRole.value === 'admin') return true
    return permissions.value.includes(permission)
}
</script>

<template>
<div class="min-h-screen flex">
    <aside class="w-64 p-6 bg-gray-100 flex flex-col">
        <div class="flex items-center space-x-4 mb-4">
            <Link :href="route('home')">
                <img src="/images/brgylogo.png" class="h-14 w-14 rounded-full" />
            </Link>
            <div>
                <h1 class="font-heading text-xl">BEL-IS</h1>
                <span class="text-gray-400 text-sm">System</span>
            </div>
        </div>
        <hr class="border-black mb-4" />
        <nav class="flex flex-col space-y-3 text-gray-600 text-base">

            <!-- Dashboard -->
            <Link v-if="can('view_dashboard')"
                href="/admindb"
                class="flex items-center space-x-2 hover:bg-gray-200 p-2 rounded-lg">
                <FontAwesomeIcon icon="gauge" />
                <span class="font-semibold">Dashboard</span>
            </Link>

            <!-- Registration -->
            <Link v-if="can('view_registration')"
                :href="route('registration')"
                class="flex items-center space-x-2 hover:bg-gray-200 p-2 rounded-lg">
                <FontAwesomeIcon icon="user-plus" />
                <span class="font-semibold">Registration</span>
            </Link>

            <!-- Visitor Records -->
            <Link v-if="can('view_visitor_records')"
                :href="route('visitor-records')"
                class="flex items-center space-x-2 hover:bg-gray-200 p-2 rounded-lg">
                <FontAwesomeIcon icon="users" />
                <span class="font-semibold">Visitor Records</span>
            </Link>

            <!-- Reports -->
            <div v-if="can('view_reports')" class="flex flex-col">
                <button @click="showReports = !showReports"
                    class="w-full flex items-center justify-between hover:bg-gray-200 p-2 rounded-lg">
                    <div class="flex items-center space-x-2">
                        <FontAwesomeIcon icon="chart-bar" />
                        <span class="font-semibold">Reports</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showReports }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
                <div v-if="showReports" class="ml-8 mt-2 flex flex-col gap-2">
                    <Link :href="route('reports.analytics')"
                        class="font-semibold border-2 border-gray-200 hover:bg-gray-200 p-2 rounded-lg text-sm">
                        Analytics
                    </Link>
                    <Link :href="route('reports.fee-revenue')"
                        class="font-semibold border-2 border-gray-200 hover:bg-gray-200 p-2 rounded-lg text-sm">
                        Fee Revenue
                    </Link>
                </div>
            </div>

            <!-- Settings -->
            <Link v-if="can('view_settings') || can('view_system_settings') || can('view_user_management')"
                :href="route('settings')"
                class="flex items-center space-x-2 hover:bg-gray-200 p-2 rounded-lg">
                <FontAwesomeIcon icon="cog" />
                <span class="font-semibold">Settings</span>
            </Link>

        </nav>
        <PrimaryButton
            @click="logout"
            :disabled="logoutForm.processing"
            class="mt-auto bg-gray-800 text-white hover:bg-gray-900 w-full">
            Logout
        </PrimaryButton>
    </aside>
    <main class="flex-1 p-8 bg-gray-50"><slot /></main>
</div>
</template>