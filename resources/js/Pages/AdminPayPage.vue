<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    visitor: Object,
})

const openFeeType = ref(false)
const openVisitorCount = ref(false)

const form = useForm({
    fee_type:           'Standard',
    number_of_visitors: 1,
    payment_method:     'Cash',
    waiver_reason:      '',   // ✅ Phase 4 Step 8 — required when fee_type = Waived
    notes:              '',
})

const feeTypeOptions      = ['Regular', 'Senior Citizen', 'Child (0 - 12 years old)']
const visitorCountOptions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]

// ── Phase 4 Step 8: Mandatory waiver reasons ──────────────────────────────────
// Every free entry must be legally justified for LGU audit (no ghost entries).
const waiverReasonOptions = [
    'Resident',
    'Official Business',
    'Child (below 12)',
    'PWD (Person with Disability)',
    'Senior Citizen',
    'Barangay Official',
    'Other (see notes)',
]

const selectFeeType = (val) => {
    form.fee_type = val
    // Clear waiver reason when switching away from Waived
    if (val !== 'Waived') form.waiver_reason = ''
    openFeeType.value = false
}

const selectVisitorCount = (val) => {
    form.number_of_visitors = val
    openVisitorCount.value  = false
}

const totalAmount = computed(() => {
    if (form.fee_type === 'Waived') return 0
    return 100 * form.number_of_visitors
})

const submit = () => {
    form.post(route('adminpay.store', props.visitor.id))
}
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
                    <span class="bg-gray-800 text-white text-sm font-bold w-6 h-6 flex items-center justify-center rounded-full">2</span>
                    <h2 class="text-gray-800 font-medium text-sm">Payment</h2>
                </div>
                <hr class="flex-1 mx-4 border-gray-300">
                <div class="flex items-center gap-2">
                    <span class="bg-gray-200 text-sm font-bold w-6 h-6 flex items-center justify-center rounded-full">3</span>
                    <h2 class="text-gray-400 font-medium text-sm">Receipt</h2>
                </div>
            </div>

            <!-- Visitor Summary -->
            <div class="w-full mt-2 bg-white p-4 rounded-lg text-sm text-gray-700 space-y-1 max-w-2xl">
                <p><span class="font-bold">Visitor:</span> {{ visitor.full_name }}</p>
                <p><span class="font-bold">Origin:</span> {{ visitor.place_of_origin }}</p>
                <p><span class="font-bold">Purpose:</span> {{ visitor.purpose }}</p>
                <p><span class="font-bold">Duration:</span> {{ visitor.duration }}</p>
            </div>

            <!-- Form -->
            <div class="w-full mt-4 max-w-2xl">
                <form @submit.prevent="submit" class="bg-white p-6 rounded-lg shadow-sm space-y-6">

                    <!-- Global error -->
                    <div v-if="form.errors.error" class="bg-red-100 text-red-700 p-3 rounded text-sm">
                        {{ form.errors.error }}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Fee Type -->
                        <div class="relative">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tourist Type</label>
                            <button type="button" @click="openFeeType = !openFeeType"
                                class="w-full border py-2 px-3 rounded text-left bg-white text-sm">
                                {{ form.fee_type || 'Select fee type' }}
                            </button>
                            <ul v-show="openFeeType"
                                class="absolute z-10 w-full mt-1 border rounded bg-white shadow-md max-h-60 overflow-auto">
                                <li v-for="option in feeTypeOptions" :key="option"
                                    @click="selectFeeType(option)"
                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                                    {{ option }}
                                </li>
                            </ul>
                            <p v-if="form.errors.fee_type" class="text-red-500 text-xs mt-1">{{ form.errors.fee_type }}</p>
                        </div>

                        <div v-if="form.fee_type !== 'Waived'" class="relative">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Environmental Fee</label>
                            <input
                                v-model="form.environmental_fee"
                                type="number"
                                class="w-full border py-2 px-3 rounded bg-white text-sm border-gray-300 focus:outline-none focus:ring-0 focus:border-gray-300"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Notes <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <textarea v-model="form.notes"
                                class="border rounded w-full py-2 px-3 text-sm"
                                rows="3" placeholder="Any additional notes..."></textarea>
                        </div>
                    </div>

                    <!-- ✅ Phase 4 Step 8: Waiver Reason — mandatory when fee_type = Waived -->
                    <!-- Prevents ghost entries; every free entry must be legally justified -->
                    <div v-if="form.fee_type === 'Waived'"
                        class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm font-bold text-amber-800">Waiver Reason Required</p>
                        </div>
                        <p class="text-xs text-amber-700">
                            Every waived fee must be recorded for LGU audit compliance. Select the applicable reason below.
                        </p>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Reason for Waiver <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 gap-2">
                                <label v-for="reason in waiverReasonOptions" :key="reason"
                                    class="flex items-center gap-3 p-2 rounded border cursor-pointer transition"
                                    :class="form.waiver_reason === reason
                                        ? 'border-amber-400 bg-amber-100'
                                        : 'border-gray-200 hover:bg-gray-50'">
                                    <input type="radio" :value="reason" v-model="form.waiver_reason"
                                        class="form-radio text-amber-500" />
                                    <span class="text-sm text-gray-800">{{ reason }}</span>
                                </label>
                            </div>
                            <p v-if="form.errors.waiver_reason" class="text-red-500 text-xs mt-2">
                                {{ form.errors.waiver_reason }}
                            </p>
                        </div>
                    </div>

                    <!-- Total Amount Preview -->
                    <div class="rounded-lg p-4 text-sm"
                        :class="form.fee_type === 'Waived'
                            ? 'bg-amber-50 border border-amber-200'
                            : 'bg-green-50 border border-green-200'">
                        <div class="flex justify-between font-bold text-gray-800">
                            <span>Total Amount Due:</span>
                            <span :class="form.fee_type === 'Waived' ? 'text-amber-600' : 'text-green-700'">
                                {{ form.fee_type === 'Waived' ? 'Waived' : `PHP ${totalAmount.toFixed(2)}` }}
                            </span>
                        </div>
                        <p class="text-gray-500 text-xs mt-1">
                            <template v-if="form.fee_type === 'Waived'">
                                Reason: {{ form.waiver_reason || '— not yet selected' }}
                            </template>
                            <template v-else>
                                PHP 100.00 × {{ form.number_of_visitors }} visitor(s)
                            </template>
                        </p>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-center">
                        <button type="submit" :disabled="form.processing"
                            class="bg-gray-900 text-white font-bold py-2 px-6 rounded disabled:opacity-50 text-sm">
                            {{ form.processing ? 'Processing...' : 'Collect Payment' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </LandingLayout>
</template>