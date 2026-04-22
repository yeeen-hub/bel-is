<script setup>
import { computed, ref } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    visitor:       Object,
    groupMembers:  { type: Array,   default: () => [] },
    isGroup:       { type: Boolean, default: false },
    totalDue:      { type: Number,  default: 0 },
    feeCategories: { type: Array,   default: () => [] },
})

// ── Permission check ──────────────────────────────────────────────────────────
const page        = usePage()
const permissions = computed(() => page.props.auth?.permissions ?? [])
const userRole    = computed(() => (page.props.auth?.user?.role ?? '').toLowerCase())
const can = (permission) => {
    if (userRole.value === 'admin') return true
    return permissions.value.includes(permission)
}

// ── Payment form ──────────────────────────────────────────────────────────────
const form = useForm({
    fee_type:       'Collected',
    payment_method: 'Cash',
    waiver_reason:  '',
    notes:          '',
})

const waiverReasonOptions = [
    'Resident', 'Official Business', 'Child (below 12)',
    'PWD (Person with Disability)', 'Senior Citizen',
    'Barangay Official', 'Other (see notes)',
]

const totalAmount = computed(() =>
    form.fee_type === 'Waived' ? 0 : props.totalDue
)

const submit = () => form.post(route('adminpay.store', props.visitor.id))

// ── No Show ───────────────────────────────────────────────────────────────────
const showNoShowConfirm = ref(false)
const noShowProcessing  = ref(false)

const confirmNoShow = () => { showNoShowConfirm.value = true }
const cancelNoShow  = () => { showNoShowConfirm.value = false }

const submitNoShow = () => {
    noShowProcessing.value = true
    router.post(route('adminpay.no-show', props.visitor.id), {}, {
        onFinish: () => { noShowProcessing.value = false },
    })
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

            <!-- Individual Summary -->
            <div v-if="!isGroup" class="w-full mt-2 bg-white p-4 rounded-lg text-sm text-gray-700 space-y-1 max-w-2xl">
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

            <!-- Group Summary -->
            <div v-else class="w-full mt-2 bg-white rounded-lg max-w-2xl overflow-hidden">
                <div class="px-4 pt-3 pb-1 flex items-center gap-2 border-b border-gray-100">
                    <span class="text-sm font-bold text-gray-800">Group Registration</span>
                    <span class="text-xs font-bold bg-gray-800 text-white px-2 py-0.5 rounded-full">
                        {{ groupMembers.length }} visitor(s)
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[480px]">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 w-8">#</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Origin</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Category</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(m, i) in groupMembers" :key="m.id"
                                class="border-b border-gray-50 last:border-0 hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-xs text-gray-400">{{ i + 1 }}</td>
                                <td class="px-4 py-2.5 font-medium text-gray-800">
                                    {{ m.full_name }}
                                    <span v-if="i === 0" class="ml-1 text-xs bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded-full">Leader</span>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">{{ m.place_of_origin }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                        {{ m.visitor_category || 'Not set' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-xs font-semibold text-green-700">
                                    ₱{{ Number(m.category_fee ?? 0).toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                <td colspan="4" class="px-4 py-2.5 text-sm font-bold text-gray-700">Subtotal</td>
                                <td class="px-4 py-2.5 text-right text-sm font-bold text-green-700">
                                    ₱{{ Number(totalDue).toFixed(2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="px-4 py-2.5 text-xs text-gray-500 border-t border-gray-100 flex flex-wrap gap-4">
                    <span><span class="font-semibold text-gray-600">Purpose:</span> {{ visitor.purpose }}</span>
                    <span><span class="font-semibold text-gray-600">Duration:</span> {{ visitor.duration }}</span>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="w-full mt-4 max-w-2xl">
                <form @submit.prevent="submit" class="bg-white p-6 rounded-lg shadow-sm space-y-6">

                    <div v-if="form.errors.error" class="bg-red-100 text-red-700 p-3 rounded text-sm">
                        {{ form.errors.error }}
                    </div>

                    <!-- Fee Type (read-only) -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Fee Type</label>
                        <div class="flex flex-wrap gap-2">
                            <span v-if="!isGroup"
                                class="inline-flex items-center bg-gray-100 text-gray-700 text-sm font-semibold px-4 py-2 rounded-lg">
                                {{ visitor.visitor_category || 'Standard' }}
                            </span>
                            <template v-else>
                                <span v-for="m in groupMembers" :key="m.id"
                                    class="inline-flex items-center gap-1.5 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                                    {{ m.full_name.split(' ')[0] }}: {{ m.visitor_category || 'N/A' }}
                                    <span class="text-green-700">₱{{ Number(m.category_fee ?? 0).toFixed(2) }}</span>
                                </span>
                            </template>
                            <span class="text-xs text-gray-400 self-center ml-1">
                                (set during registration — not editable)
                            </span>
                        </div>
                    </div>

                    <!-- Number of Visitors (read-only) -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Number of Visitors</label>
                        <div class="border border-gray-200 rounded bg-gray-50 py-2 px-3 text-sm text-gray-700 flex items-center justify-between">
                            <span class="font-semibold">{{ groupMembers.length || 1 }}</span>
                            <span class="text-gray-400 text-xs">visitor(s) registered</span>
                        </div>
                    </div>

                    <!-- ── Payment Status toggle ── -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Payment Status</label>
                        <div class="grid gap-3" :class="can('edit_payment') ? 'grid-cols-3' : 'grid-cols-2'">

                            <button type="button"
                                @click="form.fee_type = 'Collected'; form.waiver_reason = ''; showNoShowConfirm = false"
                                :class="form.fee_type === 'Collected' && !showNoShowConfirm
                                    ? 'bg-green-600 text-white border-green-600'
                                    : 'bg-white text-gray-600 border-gray-300 hover:border-green-400'"
                                class="border-2 rounded-lg py-2.5 text-sm font-semibold transition">
                                ✓ Collect Payment
                            </button>

                            <button type="button"
                                @click="form.fee_type = 'Waived'; showNoShowConfirm = false"
                                :class="form.fee_type === 'Waived' && !showNoShowConfirm
                                    ? 'bg-amber-500 text-white border-amber-500'
                                    : 'bg-white text-gray-600 border-gray-300 hover:border-amber-400'"
                                class="border-2 rounded-lg py-2.5 text-sm font-semibold transition">
                                ⊘ Waive Fee
                            </button>

                            <!-- No Show — only visible with edit_payment permission -->
                            <button v-if="can('edit_payment')" type="button"
                                @click="confirmNoShow"
                                :class="showNoShowConfirm
                                    ? 'bg-red-600 text-white border-red-600'
                                    : 'bg-white text-gray-600 border-gray-300 hover:border-red-400'"
                                class="border-2 rounded-lg py-2.5 text-sm font-semibold transition">
                                ✕ No Show
                            </button>

                        </div>
                    </div>

                    <!-- ── No Show Confirmation Panel ── -->
                    <div v-if="showNoShowConfirm"
                        class="bg-red-50 border border-red-200 rounded-lg p-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm font-bold text-red-800">Mark as No Show?</p>
                        </div>
                        <p class="text-xs text-red-700">
                            This will permanently mark
                            <span class="font-semibold">
                                {{ isGroup ? `all ${groupMembers.length} visitor(s) in this group` : visitor.full_name }}
                            </span>
                            as <span class="font-bold">No Show</span>.
                            The record will be closed and no payment can be collected afterwards.
                        </p>
                        <div class="flex gap-3 pt-1">
                            <button type="button" @click="cancelNoShow"
                                class="flex-1 text-sm font-semibold border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-100 transition">
                                Cancel
                            </button>
                            <button type="button" @click="submitNoShow" :disabled="noShowProcessing"
                                class="flex-1 text-sm font-bold bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition disabled:opacity-60">
                                {{ noShowProcessing ? 'Processing...' : 'Confirm No Show' }}
                            </button>
                        </div>
                    </div>

                    <!-- Notes (hidden during No Show confirm) -->
                    <div v-if="!showNoShowConfirm">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Notes <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <textarea v-model="form.notes"
                            class="border rounded w-full py-2 px-3 text-sm border-gray-300"
                            rows="3" placeholder="Any additional notes..."></textarea>
                    </div>

                    <!-- Waiver reason (hidden during No Show confirm) -->
                    <div v-if="form.fee_type === 'Waived' && !showNoShowConfirm"
                        class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm font-bold text-amber-800">Waiver Reason Required</p>
                        </div>
                        <p class="text-xs text-amber-700">Every waived fee must be recorded for LGU audit compliance.</p>
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

                    <!-- Total Amount Due (hidden during No Show confirm) -->
                    <div v-if="!showNoShowConfirm" class="rounded-lg p-4 text-sm"
                        :class="form.fee_type === 'Waived'
                            ? 'bg-amber-50 border border-amber-200'
                            : 'bg-green-50 border border-green-200'">
                        <div class="flex justify-between font-bold text-gray-800 text-base">
                            <span>Total Amount Due:</span>
                            <span :class="form.fee_type === 'Waived' ? 'text-amber-600' : 'text-green-700'">
                                {{ form.fee_type === 'Waived' ? 'Waived' : `PHP ${Number(totalAmount).toFixed(2)}` }}
                            </span>
                        </div>

                        <template v-if="form.fee_type !== 'Waived' && !isGroup">
                            <p class="text-gray-500 text-xs mt-1">
                                {{ visitor.visitor_category || 'Category' }} — ₱{{ Number(visitor.category_fee ?? 0).toFixed(2) }}
                            </p>
                            <div class="mt-3 pt-3 border-t border-green-200 space-y-1">
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>Category</span><span>{{ visitor.visitor_category || '—' }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>Fee per head</span><span>₱{{ Number(visitor.category_fee ?? 0).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>No. of visitors</span><span>× 1</span>
                                </div>
                                <div class="flex justify-between text-xs font-bold text-gray-800 pt-1 border-t border-green-200">
                                    <span>Total</span>
                                    <span class="text-green-700">₱{{ Number(totalAmount).toFixed(2) }}</span>
                                </div>
                            </div>
                        </template>

                        <template v-else-if="form.fee_type !== 'Waived' && isGroup">
                            <p class="text-gray-500 text-xs mt-1">
                                {{ groupMembers.length }} visitor(s) — fees computed from individual categories
                            </p>
                            <div class="mt-3 pt-3 border-t border-green-200 space-y-1">
                                <div v-for="m in groupMembers" :key="m.id"
                                    class="flex justify-between text-xs text-gray-600">
                                    <span>{{ m.full_name }} <span class="text-gray-400 ml-1">({{ m.visitor_category || 'No cat.' }})</span></span>
                                    <span>₱{{ Number(m.category_fee ?? 0).toFixed(2) }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-bold text-gray-800 pt-1 border-t border-green-200">
                                    <span>Total</span>
                                    <span class="text-green-700">₱{{ Number(totalAmount).toFixed(2) }}</span>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <p class="text-gray-500 text-xs mt-1">
                                Reason: {{ form.waiver_reason || '— not yet selected' }}
                            </p>
                        </template>
                    </div>

                    <!-- Submit (hidden during No Show confirm) -->
                    <div v-if="!showNoShowConfirm" class="flex justify-center">
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