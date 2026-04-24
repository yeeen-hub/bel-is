<template>
    <LandingLayout>

        <!-- ── Top Bar ───────────────────────────────────────────────────────── -->
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-3">

                <div class="relative flex-1">
                    <input v-model="search" type="text"
                        placeholder="Search..."
                        :class="[
                            'w-full p-2 pl-8 rounded-lg border text-sm transition-colors duration-200',
                            search
                                ? 'border-gray-800 bg-white ring-1 ring-gray-800'
                                : 'border-gray-300 bg-white focus:border-gray-400'
                        ]" />
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4"
                        :class="search ? 'text-gray-800' : 'text-gray-400'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
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
                                    <button
                                        @click="feeStatus = 'Pending'; showNotifications = false; applyFilters()"
                                        class="text-xs text-yellow-600 font-semibold mt-1 inline-block hover:underline">
                                        Show Pending Records →
                                    </button>
                                </div>
                            </div>
                            <div v-if="pendingFees === 0"
                                class="px-4 py-8 text-center text-gray-400 text-sm">
                                <FontAwesomeIcon icon="bell" class="text-gray-300 text-2xl mb-2 block mx-auto" />
                                <p>No new notifications</p>
                            </div>
                        </div>
                    </div>
                </div>

                <FontAwesomeIcon icon="user" class="text-gray-700" />
            </div>
        </div>

        <!-- ── Page Body ──────────────────────────────────────────────────────── -->
        <div class="container mx-auto bg-gray-100 rounded-lg">

            <!-- Page Title -->
            <div class="p-4 bg-gray-100 rounded-lg mt-4">
                <h1 class="font-heading text-gray-800 font-semibold text-2xl">Dashboard</h1>
            </div>

            <!-- ── Row 1: Primary Stats ─────────────────────────────────────── -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 p-4 gap-4 mt-2">

                <div class="bg-gray-900 p-4 rounded-lg shadow-md">
                    <h2 class="text-white text-sm">Total Tourists</h2>
                    <p class="text-2xl font-bold text-white">
                        {{ stats.total_tourists.toLocaleString() }}
                    </p>
                    <p class="text-white text-xs mt-1">
                        {{ stats.total_tourists_today }} tourist(s) today
                    </p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-gray-800 font-medium text-sm">Total Resorts</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ stats.total_attractions }}</p>
                    <p class="text-gray-500 text-xs mt-1">Total resorts available</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-gray-800 font-medium text-sm">Total Accommodations</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ stats.total_packages }}</p>
                    <p class="text-gray-500 text-xs mt-1">Total accommodations available</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-gray-800 font-medium text-sm">Total Beaches</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ stats.total_circuits }}</p>
                    <p class="text-gray-500 text-xs mt-1">Total beaches available</p>
                </div>

            </div>

            <!-- ── Row 2: Revenue Stats ─────────────────────────────────────── -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 p-4 gap-4">

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-gray-800 font-medium text-sm">This Month</h2>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ stats.visitors_this_month.toLocaleString() }}
                    </p>
                    <p class="text-gray-500 text-xs mt-1">Visitors this month</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-gray-800 font-medium text-sm">Revenue Today</h2>
                        <span v-if="stats.revenue_is_estimated"
                            class="text-xs bg-yellow-100 text-yellow-700 font-semibold px-1.5 py-0.5 rounded">
                            est.
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">
                        ₱{{ Number(stats.revenue_today).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                    </p>
                    <p class="text-gray-500 text-xs mt-1">
                        ₱{{ Number(stats.revenue_this_month).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                        this month
                    </p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-gray-800 font-medium text-sm">Revenue This Year</h2>
                        <span v-if="stats.revenue_is_estimated"
                            class="text-xs bg-yellow-100 text-yellow-700 font-semibold px-1.5 py-0.5 rounded">
                            est.
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">
                        ₱{{ Number(stats.revenue_this_year).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                    </p>
                    <p class="text-gray-500 text-xs mt-1">
                        {{ stats.revenue_is_estimated ? 'Based on collected fee_status × ₱100' : 'Total environmental fees collected' }}
                    </p>
                </div>

                <div class="p-4 rounded-lg shadow-md border-2 transition" :class="stats.pending_fees > 0
                    ? 'bg-yellow-50 border-yellow-300'
                    : 'bg-white border-transparent'">
                    <h2 class="font-medium text-sm"
                        :class="stats.pending_fees > 0 ? 'text-yellow-800' : 'text-gray-800'">
                        Pending Fees
                    </h2>
                    <p class="text-2xl font-bold" :class="stats.pending_fees > 0 ? 'text-yellow-700' : 'text-gray-800'">
                        {{ stats.pending_fees }}
                    </p>
                    <p class="text-xs mt-1" :class="stats.pending_fees > 0 ? 'text-yellow-600' : 'text-gray-500'">
                        {{ stats.pending_fees > 0 ? 'Require immediate attention' : 'All fees collected ✓' }}
                    </p>
                </div>

            </div>

            <!-- ── Pre-Registration Info Banner ────────────────────────────── -->
            <!-- ✅ Informational only — no action button, no process link     -->
            <div v-if="stats.pending_pre_reg > 0" class="px-4 pb-2">
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-blue-800">
                            {{ stats.pending_pre_reg }} potential visitor(s) have pre-registered online
                        </p>
                        <p class="text-xs text-blue-600 mt-0.5">
                            They may be on their way. Process them only when they arrive and present their reference
                            code at the checkpoint.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Row 3: Charts ───────────────────────────────────────────── -->
            <div class="grid grid-cols-1 md:grid-cols-2 p-4 gap-4 mt-2">

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-gray-800 font-medium text-sm mb-4">Visitors Per Day (Last 7 Days)</h2>
                    <div v-if="visitorsPerDay.length">
                        <div class="flex items-end gap-2" style="height: 120px;">
                            <div v-for="day in visitorsPerDay" :key="day.date"
                                class="flex flex-col items-center justify-end flex-1 h-full">
                                <span class="text-xs text-gray-500 mb-1 leading-none">{{ day.count }}</span>
                                <div class="w-full bg-gray-900 rounded-t transition-all duration-500"
                                    :style="{ height: barHeightPx(day.count, visitorsPerDay) }"></div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <div v-for="day in visitorsPerDay" :key="'lbl-' + day.date"
                                class="flex-1 text-center text-xs text-gray-400">
                                {{ day.day }}
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-sm text-center py-8">No data available</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-gray-800 font-medium text-sm mb-4">Visitors Per Month (Last 6 Months)</h2>
                    <div v-if="visitorsPerMonth.length">
                        <div class="flex items-end gap-2" style="height: 120px;">
                            <div v-for="month in visitorsPerMonth" :key="month.month"
                                class="flex flex-col items-center justify-end flex-1 h-full">
                                <span class="text-xs text-gray-500 mb-1 leading-none">{{ month.count }}</span>
                                <div class="w-full bg-blue-500 rounded-t transition-all duration-500"
                                    :style="{ height: barHeightPx(month.count, visitorsPerMonth) }"></div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <div v-for="month in visitorsPerMonth" :key="'lbl-' + month.month"
                                class="flex-1 text-center text-xs text-gray-400">
                                {{ month.month.split(' ')[0] }}
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-sm text-center py-8">No data available</p>
                </div>

            </div>

            <!-- ── Row 4: Top Origins & Purpose Breakdown ──────────────────── -->
            <div class="grid grid-cols-1 md:grid-cols-2 p-4 gap-4 mt-2">

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-gray-800 font-medium text-sm mb-4">Top 5 Places of Origin</h2>
                    <div v-if="topOrigins.length" class="space-y-3">
                        <div v-for="(origin, index) in topOrigins" :key="origin.origin">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-gray-400 w-4">{{ index + 1 }}</span>
                                    <span class="text-gray-700 truncate max-w-[180px]">{{ origin.origin }}</span>
                                </div>
                                <span class="font-bold text-gray-800 text-xs ml-2">{{ origin.count }}</span>
                            </div>
                            <div class="bg-gray-100 rounded-full h-1.5 w-full">
                                <div class="bg-gray-900 h-1.5 rounded-full"
                                    :style="{ width: progressWidth(origin.count, topOrigins) }"></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-sm text-center py-4">No data available</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-gray-800 font-medium text-sm mb-4">Visit Purpose Breakdown</h2>
                    <div v-if="purposeBreakdown.length" class="space-y-3">
                        <div v-for="item in purposeBreakdown" :key="item.purpose">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-700">{{ item.purpose }}</span>
                                <span class="font-bold text-gray-800 text-xs">{{ item.count }}</span>
                            </div>
                            <div class="bg-gray-100 rounded-full h-1.5 w-full">
                                <div class="bg-blue-500 h-1.5 rounded-full"
                                    :style="{ width: progressWidth(item.count, purposeBreakdown) }"></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-sm text-center py-4">No data available</p>
                </div>

            </div>

            <!-- ── Row 5: Recent Tourist Table ────────────────────────────── -->
            <div class="p-4 mt-2">
                <div class="w-full p-4 shadow-md rounded bg-white">

                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-lg font-semibold">Recent Tourist Table</h1>
                        <Link :href="route('visitor-records')"
                            class="text-sm text-gray-500 hover:text-gray-800 underline">
                            View All
                        </Link>
                    </div>

                    <div class="overflow-x-auto">

                        <table class="min-w-[700px] w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="text-gray-500 text-xs uppercase">
                                    <th class="p-2 border-b">Name</th>
                                    <th class="p-2 border-b">Place of Origin</th>
                                    <th class="p-2 border-b">Purpose of Visit</th>
                                    <th class="p-2 border-b">Duration of Stay</th>
                                    <th class="p-2 border-b">Fee Status</th>
                                    <th class="p-2 border-b">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="recentVisitors.length === 0">
                                    <td colspan="6" class="p-4 text-center text-gray-400">
                                        No tourist records yet.
                                    </td>
                                </tr>
                                <tr v-for="visitor in recentVisitors" :key="visitor.id" class="hover:bg-gray-50">
                                    <td class="p-2 border-b font-medium text-gray-800">{{ visitor.name }}</td>
                                    <td class="p-2 border-b text-gray-600">{{ visitor.place_of_origin }}</td>
                                    <td class="p-2 border-b text-gray-600">{{ visitor.purpose }}</td>
                                    <td class="p-2 border-b text-gray-600">{{ visitor.duration }}</td>
                                    <td class="p-2 border-b">
                                        <div class="relative inline-block" :ref="el => setDropdownRef(el, visitor.id)">
                                            <button @click.stop="toggleFeeDropdown(visitor.id)" :class="{
                                                'bg-green-100 text-green-700': visitor.fee_status === 'Collected',
                                                'bg-yellow-100 text-yellow-700': visitor.fee_status === 'Pending',
                                                'bg-gray-100 text-gray-500': visitor.fee_status === 'Waived',
                                            }"
                                                class="px-2 py-1 rounded-full text-xs font-bold cursor-pointer flex items-center gap-1 hover:opacity-80">
                                                {{ visitor.fee_status }}
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div v-if="activeFeeDropdown === visitor.id"
                                                class="absolute left-0 mt-1 w-36 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                                                <Link v-if="visitor.fee_status === 'Pending'"
                                                    :href="route('adminpay', visitor.id)"
                                                    class="flex items-center gap-2 px-3 py-2 text-xs text-yellow-700 hover:bg-yellow-50 rounded-t-lg">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Collect Fee
                                                </Link>
                                                <Link
                                                    v-if="visitor.fee_status === 'Collected' || visitor.fee_status === 'Waived'"
                                                    :href="route('adminreceipt', visitor.id)"
                                                    class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-t-lg">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    View Receipt
                                                </Link>
                                                <Link :href="route('visitor-records.show', visitor.id)"
                                                    class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-b-lg border-t border-gray-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    View Details
                                                </Link>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 border-b text-gray-500 text-xs">{{ visitor.arrival_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </LandingLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_tourists: 0,
            total_tourists_today: 0,
            visitors_this_month: 0,
            pending_fees: 0,
            pending_pre_reg: 0,
            revenue_today: 0,
            revenue_this_month: 0,
            revenue_this_year: 0,
            total_attractions: 0,
            total_packages: 0,
            total_circuits: 0,
            revenue_is_estimated: false,
        }),
    },
    visitorsPerDay: { type: Array, default: () => [] },
    visitorsPerMonth: { type: Array, default: () => [] },
    purposeBreakdown: { type: Array, default: () => [] },
    topOrigins: { type: Array, default: () => [] },
    recentVisitors: { type: Array, default: () => [] },
})

const showNotifications = ref(false)
const bellRef = ref(null)

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value
    activeFeeDropdown.value = null
}

const activeFeeDropdown = ref(null)
const dropdownRefs = {}

const setDropdownRef = (el, id) => {
    if (el) dropdownRefs[id] = el
}

const toggleFeeDropdown = (id) => {
    activeFeeDropdown.value = activeFeeDropdown.value === id ? null : id
    showNotifications.value = false
}

const handleClickOutside = (e) => {
    if (bellRef.value && !bellRef.value.contains(e.target)) {
        showNotifications.value = false
    }
    const activeRef = dropdownRefs[activeFeeDropdown.value]
    if (activeRef && !activeRef.contains(e.target)) {
        activeFeeDropdown.value = null
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

const barHeightPx = (count, dataset, maxPx = 88) => {
    const max = Math.max(...dataset.map(d => d.count), 1)
    const minPx = 4
    if (count === 0) return `${minPx}px`
    return `${Math.max(minPx, Math.round((count / max) * maxPx))}px`
}

const progressWidth = (count, dataset) => {
    const max = Math.max(...dataset.map(d => d.count), 1)
    return `${Math.max(5, Math.round((count / max) * 100))}%`
}
</script>