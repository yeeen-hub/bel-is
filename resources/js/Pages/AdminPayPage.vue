<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    visitor:        Object,   // includes visitor_category and category_fee
    feeCategories:  { type: Array, default: () => [] },
})

const openFeeType       = ref(false)
const openCategoryOverride = ref(false)

// ── Resolve the initial fee per head from the visitor's category ──────────────
// visitor.category_fee is pre-resolved server-side; used as the default.
const feePerHead = computed(() => {
    if (form.fee_type === 'Waived') return 0
    // If staff overrides the category, resolve from feeCategories
    if (form.visitor_category) {
        const cat = props.feeCategories.find(c => c.category === form.visitor_category)
        return cat ? Number(cat.fee) : Number(props.visitor.category_fee ?? 0)
    }
    return Number(props.visitor.category_fee ?? 0)
})

const form = useForm({
    fee_type:           'Standard',
    number_of_visitors: 1,
    payment_method:     'Cash',
    waiver_reason:      '',
    notes:              '',
    // visitor_category can be overridden at payment time (e.g. if staff missed it)
    visitor_category:   props.visitor.visitor_category ?? '',
    // amount_per_head is computed and sent for server-side verification
    amount_per_head:    props.visitor.category_fee ?? 0,
})

const feeTypeOptions = ['Standard', 'Group', 'Waived']

const waiverReasonOptions = [
    'Resident',
    'Official Business',
    'Child (below 12)',
    'PWD (Person with Disability)',
    'Senior Citizen',
    'Barangay Official',
    'Other (see notes)',
]

const visitorCountOptions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20]

const openVisitorCount = ref(false)

const selectFeeType = (val) => {
    form.fee_type = val
    if (val !== 'Waived') form.waiver_reason = ''
    openFeeType.value = false
}

const selectVisitorCount = (val) => {
    form.number_of_visitors = val
    openVisitorCount.value  = false
}

// ── Autonomous total: fee per head × number of visitors ──────────────────────
const totalAmount = computed(() => {
    if (form.fee_type === 'Waived') return 0
    return feePerHead.value * form.number_of_visitors
})

// ── Resolved category label for display ──────────────────────────────────────
const resolvedCategoryLabel = computed(() => {
    const cat = props.feeCategories.find(c => c.category === form.visitor_category)
    if (!cat) return form.visitor_category || '—'
    return cat.age_range
        ? `${cat.category} (${cat.age_range})`
        : cat.category
})

const submit = () => {
    form.amount_per_head = feePerHead.value
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
                <p>
                    <span class="font-bold">Category:</span>
                    <span class="ml-1 inline-flex items-center bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                        {{ visitor.visitor_category || 'Not set' }}
                    </span>
                    <span v-if="visitor.category_fee" class="ml-2 text-green-700 font-semibold text-xs">
                        ₱{{ Number(visitor.category_fee).toFixed(2) }} / head
                    </span>
                </p>
            </div>

            <!-- Form -->
            <div class="w-full mt-4 max-w-2xl">
                <form @submit.prevent="submit" class="bg-white p-6 rounded-lg shadow-sm space-y-6">

                    <div v-if="form.errors.error" class="bg-red-100 text-red-700 p-3 rounded text-sm">
                        {{ form.errors.error }}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Fee Type -->
                        <div class="relative">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Fee Type</label>
                            <button type="button" @click="openFeeType = !openFeeType"
                                class="w-full border py-2 px-3 rounded text-left bg-white text-sm border-gray-300">
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

                        <!-- Number of Visitors -->
                        <div v-if="form.fee_type !== 'Waived'" class="relative">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Number of Visitors</label>
                            <button type="button" @click="openVisitorCount = !openVisitorCount"
                                class="w-full border py-2 px-3 rounded text-left bg-white text-sm border-gray-300">
                                {{ form.number_of_visitors }}
                            </button>
                            <ul v-show="openVisitorCount"
                                class="absolute z-10 w-full mt-1 border rounded bg-white shadow-md max-h-60 overflow-auto">
                                <li v-for="n in visitorCountOptions" :key="n"
                                    @click="selectVisitorCount(n)"
                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                                    {{ n }}
                                </li>
                            </ul>
                        </div>

                        <!-- Category Override (if no category set or staff wants to change) -->
                        <div v-if="form.fee_type !== 'Waived'" class="relative md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Visitor Category
                                <span class="text-gray-400 font-normal text-xs ml-1">
                                    (change if needed — fee updates automatically)
                                </span>
                            </label>
                            <button type="button" @click="openCategoryOverride = !openCategoryOverride"
                                class="w-full border py-2 px-3 rounded text-left bg-white text-sm border-gray-300 flex items-center justify-between">
                                <span :class="form.visitor_category ? 'text-gray-800' : 'text-gray-400'">
                                    {{ form.visitor_category ? resolvedCategoryLabel : 'Select category' }}
                                </span>
                                <span v-if="form.visitor_category" class="text-green-600 font-bold text-xs ml-2">
                                    ₱{{ feePerHead.toFixed(2) }} / head
                                </span>
                            </button>
                            <ul v-show="openCategoryOverride"
                                class="absolute z-10 w-full mt-1 border rounded bg-white shadow-md max-h-60 overflow-auto">
                                <li v-for="cat in feeCategories" :key="cat.id"
                                    @click="form.visitor_category = cat.category; openCategoryOverride = false"
                                    class="px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm flex items-center justify-between border-b last:border-0"
                                    :class="form.visitor_category === cat.category ? 'bg-gray-50 font-semibold' : ''">
                                    <div>
                                        <span>{{ cat.category }}</span>
                                        <span v-if="cat.age_range" class="text-gray-400 text-xs ml-2">{{ cat.age_range }}</span>
                                    </div>
                                    <span class="text-green-700 font-bold text-xs">₱{{ cat.fee }}</span>
                                </li>
                            </ul>
                            <p v-if="form.errors.visitor_category" class="text-red-500 text-xs mt-1">{{ form.errors.visitor_category }}</p>
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Notes <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <textarea v-model="form.notes"
                                class="border rounded w-full py-2 px-3 text-sm border-gray-300"
                                rows="3" placeholder="Any additional notes..."></textarea>
                        </div>
                    </div>

                    <!-- Waiver Reason -->
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
                            Every waived fee must be recorded for LGU audit compliance.
                        </p>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Reason for Waiver <span class="text-red-500">*</span>
                            </label>
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

                    <!-- ── Autonomous Total Amount Preview ─────────────────────── -->
                    <div class="rounded-lg p-4 text-sm"
                        :class="form.fee_type === 'Waived'
                            ? 'bg-amber-50 border border-amber-200'
                            : 'bg-green-50 border border-green-200'">
                        <div class="flex justify-between font-bold text-gray-800 text-base">
                            <span>Total Amount Due:</span>
                            <span :class="form.fee_type === 'Waived' ? 'text-amber-600' : 'text-green-700'">
                                {{ form.fee_type === 'Waived' ? 'Waived' : `PHP ${totalAmount.toFixed(2)}` }}
                            </span>
                        </div>
                        <template v-if="form.fee_type !== 'Waived'">
                            <p class="text-gray-500 text-xs mt-1">
                                {{ resolvedCategoryLabel }} — ₱{{ feePerHead.toFixed(2) }}
                                × {{ form.number_of_visitors }} visitor(s)
                            </p>
                            <!-- Breakdown per visitor count -->
                            <div class="mt-3 pt-3 border-t border-green-200 space-y-1">
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>Category</span>
                                    <span>{{ resolvedCategoryLabel }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>Fee per head</span>
                                    <span>₱{{ feePerHead.toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>No. of visitors</span>
                                    <span>× {{ form.number_of_visitors }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-bold text-gray-800 pt-1 border-t border-green-200">
                                    <span>Total</span>
                                    <span class="text-green-700">₱{{ totalAmount.toFixed(2) }}</span>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <p class="text-gray-500 text-xs mt-1">
                                Reason: {{ form.waiver_reason || '— not yet selected' }}
                            </p>
                        </template>
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