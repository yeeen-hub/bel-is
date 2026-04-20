<script setup>
import LandingLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    visitor: Object,
    receipt: Object,
})

const print = () => window.print()
</script>

<template>
    <LandingLayout>
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
                <div class="relative flex-1">
                    <input type="text" placeholder="Search..."
                        class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
                </div>
                <FontAwesomeIcon icon="bell" />
                <FontAwesomeIcon icon="user" />
            </div>
        </div>

        <div class="bg-gray-100 p-4 mt-4 rounded-lg flex flex-col items-center justify-center">
            <div>
                <h1 class="font-heading text-gray-800 text-3xl">Tourist Registration</h1>
                <p class="text-sm text-gray-600 text-center">Enter the details to get going</p>
            </div>

            <!-- Step Indicator -->
            <div class="flex items-center justify-between p-4 mt-4 w-full max-w-lg">
                <div class="flex items-center gap-2">
                    <span class="bg-gray-200 text-sm font-bold w-6 h-6 flex items-center justify-center rounded-full">1</span>
                    <h2 class="text-gray-400 font-medium text-sm">General Details</h2>
                </div>
                <hr class="flex-1 mx-4 border-gray-300">
                <div class="flex items-center gap-2">
                    <span class="bg-gray-200 text-sm font-bold w-6 h-6 flex items-center justify-center rounded-full">2</span>
                    <h2 class="text-gray-400 font-medium text-sm">Payment</h2>
                </div>
                <hr class="flex-1 mx-4 border-gray-300">
                <div class="flex items-center gap-2">
                    <span class="bg-gray-800 text-white text-sm font-bold w-6 h-6 flex items-center justify-center rounded-full">3</span>
                    <h2 class="text-gray-800 font-medium text-sm">Receipt</h2>
                </div>
            </div>

            <!-- Receipt Card -->
            <div class="w-full mt-4 bg-white p-6 rounded-lg max-w-lg" id="receipt-content">

                <!-- Barangay Header -->
                <div class="text-center mb-4">
                    <img src="/images/brgylogo.png" alt="Barangay Logo" class="h-16 w-16 mx-auto mb-2 rounded-full" />
                    <p class="font-bold text-gray-800 text-lg">Barangay Bel-is</p>
                    <p class="text-sm text-gray-600">Buruanga, Aklan, Philippines</p>
                    <p class="text-xs text-gray-500 mt-1">Official Environmental Fee Receipt</p>
                </div>

                <hr class="my-3 border-gray-300">

                <!-- Status icon -->
                <div class="flex justify-center mb-2">
                    <svg v-if="receipt?.fee_type !== 'Waived'" width="36" height="36" viewBox="0 0 24 24" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z"
                            fill="#0d912e" />
                    </svg>
                    <svg v-else width="36" height="36" viewBox="0 0 24 24" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM10 7a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1zm1 4a1 1 0 100 2h.01a1 1 0 100-2H11z"
                            fill="#d97706" />
                    </svg>
                </div>

                <h2 class="text-center text-gray-800 font-bold text-lg">
                    {{ receipt?.fee_type === 'Waived' ? 'Fee Waived' : 'Payment Successful' }}
                </h2>
                <p class="text-center text-gray-500 text-xs mt-1 mb-4">
                    Receipt No: <span class="font-mono font-bold">{{ receipt?.receipt_number ?? 'N/A' }}</span>
                </p>

                <hr class="my-3 border-dashed border-gray-300">

                <!-- Visitor Details -->
                <h3 class="text-gray-700 font-bold text-sm uppercase tracking-wide mb-2">Visitor Details</h3>
                <div class="space-y-1 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Name</span>
                        <span class="font-medium text-gray-800">{{ visitor?.full_name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Place of Origin</span>
                        <span class="font-medium text-gray-800">{{ visitor?.place_of_origin ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Purpose of Visit</span>
                        <span class="font-medium text-gray-800">{{ visitor?.purpose ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Duration of Stay</span>
                        <span class="font-medium text-gray-800">{{ visitor?.duration ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date of Arrival</span>
                        <span class="font-medium text-gray-800">{{ visitor?.arrival_at ?? '—' }}</span>
                    </div>
                    <!-- Visitor Category -->
                    <div class="flex justify-between">
                        <span class="text-gray-500">Visitor Category</span>
                        <span class="font-medium text-gray-800">
                            {{ visitor?.visitor_category ?? receipt?.fee_type ?? '—' }}
                        </span>
                    </div>
                </div>

                <hr class="my-3 border-dashed border-gray-300">

                <!-- Payment Details -->
                <h3 class="text-gray-700 font-bold text-sm uppercase tracking-wide mb-2">Payment Details</h3>
                <div class="space-y-1 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Fee Type</span>
                        <span class="font-medium text-gray-800">{{ receipt?.fee_type ?? '—' }}</span>
                    </div>

                    <!-- Waiver reason -->
                    <div v-if="receipt?.fee_type === 'Waived' && receipt?.waiver_reason" class="flex justify-between">
                        <span class="text-gray-500">Waiver Reason</span>
                        <span class="font-medium text-amber-700">{{ receipt.waiver_reason }}</span>
                    </div>

                    <template v-if="receipt?.fee_type !== 'Waived'">
                        <div class="flex justify-between">
                            <span class="text-gray-500">No. of Visitors</span>
                            <span class="font-medium text-gray-800">{{ receipt?.number_of_visitors ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Fee per Visitor</span>
                            <span class="font-medium text-gray-800">
                                PHP {{ receipt?.amount != null ? Number(receipt.amount).toFixed(2) : '—' }}
                            </span>
                        </div>
                        <!-- Calculation breakdown -->
                        <div class="flex justify-between text-xs text-gray-400 italic">
                            <span>
                                {{ visitor?.visitor_category ?? receipt?.fee_type }}
                                × {{ receipt?.number_of_visitors }}
                            </span>
                            <span>
                                = PHP {{ receipt?.total_amount != null ? Number(receipt.total_amount).toFixed(2) : '—' }}
                            </span>
                        </div>
                    </template>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Payment Method</span>
                        <span class="font-medium text-gray-800">{{ receipt?.payment_method ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date Collected</span>
                        <span class="font-medium text-gray-800">{{ receipt?.collected_at ?? '—' }}</span>
                    </div>
                </div>

                <hr class="my-3 border-gray-300">

                <!-- Total -->
                <div class="flex justify-between font-bold text-base mt-2">
                    <span class="text-gray-800">Total Amount</span>
                    <span :class="receipt?.fee_type === 'Waived' ? 'text-amber-600' : 'text-green-700'">
                        {{ receipt?.fee_type === 'Waived'
                            ? 'Waived'
                            : receipt?.total_amount != null
                                ? `PHP ${Number(receipt.total_amount).toFixed(2)}`
                                : '—' }}
                    </span>
                </div>

                <hr class="my-4 border-dashed border-gray-300">

                <p class="text-center text-xs text-gray-400 mt-2">
                    This is an official receipt from Barangay Bel-is.<br>
                    Thank you for visiting!
                </p>

                <!-- Action buttons -->
                <div id="receipt-actions" class="flex justify-center gap-4 mt-6">
                    <button @click="print"
                        class="bg-gray-900 text-white font-bold py-2 px-6 rounded hover:bg-gray-700 text-sm">
                        Print Receipt
                    </button>
                    <a :href="route('registration')"
                        class="bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded hover:bg-gray-300 text-sm">
                        New Registration
                    </a>
                </div>

            </div>
        </div>
    </LandingLayout>
</template>