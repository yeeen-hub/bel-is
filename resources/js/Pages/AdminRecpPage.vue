<script setup>
/**
 * AdminRecpPage.vue — Official Receipt (Accountable Form No. 51-C format)
 * Republic of the Philippines · Official Receipt
 */
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import LandingLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    visitor: Object,
    receipt: Object,
    isGroup: { type: Boolean, default: false },
})

const page      = usePage()
const printPage = () => window.print()

// ── Amount in words helper ────────────────────────────────────────────────────
const ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
               'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
               'Seventeen','Eighteen','Nineteen']
const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety']

function numToWords(n) {
    if (n === 0) return 'Zero'
    if (n < 20) return ones[n]
    if (n < 100) return tens[Math.floor(n/10)] + (n%10 ? ' ' + ones[n%10] : '')
    if (n < 1000) return ones[Math.floor(n/100)] + ' Hundred' + (n%100 ? ' ' + numToWords(n%100) : '')
    if (n < 1000000) return numToWords(Math.floor(n/1000)) + ' Thousand' + (n%1000 ? ' ' + numToWords(n%1000) : '')
    return numToWords(Math.floor(n/1000000)) + ' Million' + (n%1000000 ? ' ' + numToWords(n%1000000) : '')
}

const amountInWords = computed(() => {
    if (!props.receipt || props.receipt.fee_type === 'Waived') return 'Waived'
    const total = parseFloat(props.receipt.total_amount ?? 0)
    const pesos  = Math.floor(total)
    const cents  = Math.round((total - pesos) * 100)
    let words = numToWords(pesos) + ' Peso' + (pesos !== 1 ? 's' : '')
    if (cents > 0) words += ' and ' + numToWords(cents) + ' Centavo' + (cents !== 1 ? 's' : '')
    return words + ' Only'
})

// ── OR number formatted like "No. 3680424 S" ─────────────────────────────────
const orNumber = computed(() => {
    if (!props.receipt?.receipt_number) return 'N/A'
    // receipt_number is stored as e.g. "OR-2026-0000029"
    // Extract the numeric portion and format as "XXXXXXX S"
    const parts = props.receipt.receipt_number.split('-')
    const num   = parts[parts.length - 1] ?? props.receipt.receipt_number
    return `${num} S`
})

// ── Payor name ────────────────────────────────────────────────────────────────
const payorName = computed(() => {
    if (props.isGroup) {
        const leader = props.receipt?.member_breakdown?.[0]
        return leader ? `${leader.full_name} et al. (${props.receipt?.number_of_visitors ?? ''} pax)` : '—'
    }
    return props.visitor?.full_name ?? '—'
})

// Nature of collection rows for the table
const collectionRows = computed(() => {
    if (!props.receipt || props.receipt.fee_type === 'Waived') {
        return [{ description: 'Environmental Fee — WAIVED', account_code: '', amount: 0 }]
    }
    if (props.isGroup && props.receipt.member_breakdown?.length) {
        return props.receipt.member_breakdown.map(m => ({
            description:  `Environmental Fee — ${m.visitor_category || 'Visitor'} (${m.full_name})`,
            account_code: '101',
            amount:       parseFloat(m.fee ?? 0),
        }))
    }
    return [{
        description:  `Environmental Fee — ${props.visitor?.visitor_category || 'Visitor'}`,
        account_code: '101',
        amount:       parseFloat(props.receipt?.total_amount ?? 0),
    }]
})

const totalAmount = computed(() =>
    parseFloat(props.receipt?.total_amount ?? 0)
)
</script>

<template>
    <LandingLayout>
        <!-- Top Bar -->
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

            <!-- Success indicator -->
            <div class="flex flex-col items-center mb-4 mt-2">
                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-2"
                    :class="receipt?.fee_type === 'Waived' ? 'bg-amber-100' : 'bg-green-100'">
                    <svg v-if="receipt?.fee_type !== 'Waived'" class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z"/>
                    </svg>
                    <svg v-else class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2a10 10 0 100 20A10 10 0 0012 2zm0 5a1 1 0 011 1v4a1 1 0 01-2 0V8a1 1 0 011-1zm0 8a1 1 0 100 2 1 1 0 000-2z"/>
                    </svg>
                </div>
                <p class="font-bold text-gray-800">{{ receipt?.fee_type === 'Waived' ? 'Fee Waived' : 'Payment Successful' }}</p>
            </div>

            <!-- ══ Official Receipt Card (Accountable Form No. 51-C format) ══ -->
            <div class="w-full max-w-md" id="receipt-content">
                <div class="bg-white border-2 border-gray-800 rounded-sm shadow-lg overflow-hidden">

                    <!-- Header: Accountable Form label -->
                    <div class="px-4 pt-3 pb-1 border-b border-gray-300">
                        <p class="text-xs text-gray-500 italic">Accountable Form No. 51-C</p>
                        <p class="text-xs text-gray-500 italic">Revised January, 1992 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (ORIGINAL)</p>
                    </div>

                    <!-- Official Receipt header with logo + OR number -->
                    <div class="flex border-b-2 border-gray-800">
                        <!-- Logo cell -->
                        <div class="w-24 shrink-0 border-r-2 border-gray-800 flex items-center justify-center p-3">
                            <img src="/images/republic of the philippines.png" alt="Republic of the Philippines"
                                class="w-16 h-16 object-contain"
                                onerror="this.src='/images/brgylogo.png'" />
                        </div>
                        <!-- Title + OR number -->
                        <div class="flex-1 p-3">
                            <div class="text-center border-b border-gray-400 pb-2 mb-2">
                                <p class="text-sm font-bold text-gray-800 leading-tight">Official Receipt</p>
                                <p class="text-xs text-gray-600">of the</p>
                                <p class="text-xs font-bold text-gray-800">Republic of the Philippines</p>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-lg font-bold text-gray-800">N<sup>o</sup></span>
                                <span class="text-2xl font-bold tracking-widest font-mono text-gray-900">{{ orNumber }}</span>
                            </div>
                            <div class="mt-2 border-t border-gray-300 pt-1">
                                <span class="text-xs text-gray-500">Date: </span>
                                <span class="text-xs font-semibold text-gray-800">{{ receipt?.collected_at ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Agency + Fund row -->
                    <div class="flex border-b border-gray-400 text-xs">
                        <div class="flex-1 px-3 py-2 border-r border-gray-400">
                            <span class="text-gray-500">Agency: </span>
                            <span class="font-semibold text-gray-800">Barangay Bel-is, Buruanga, Aklan</span>
                        </div>
                        <div class="w-28 px-3 py-2">
                            <span class="text-gray-500">Fund: </span>
                            <span class="font-semibold text-gray-800">General</span>
                        </div>
                    </div>

                    <!-- Payor row -->
                    <div class="px-3 py-2 border-b border-gray-400 text-xs">
                        <span class="text-gray-500">Payor: </span>
                        <span class="font-semibold text-gray-800">{{ payorName }}</span>
                        <span class="ml-3 text-gray-400">· {{ visitor?.place_of_origin ?? '—' }}</span>
                    </div>

                    <!-- Nature of Collection table -->
                    <div class="border-b border-gray-400">
                        <div class="grid grid-cols-12 bg-gray-100 border-b border-gray-300 text-xs font-bold text-gray-600 px-2 py-1">
                            <div class="col-span-7 text-center">Nature of Collection</div>
                            <div class="col-span-2 text-center">Account Code</div>
                            <div class="col-span-3 text-center">Amount</div>
                        </div>

                        <!-- Collection rows -->
                        <div v-for="(row, i) in collectionRows" :key="i"
                            class="grid grid-cols-12 border-b border-gray-200 text-xs px-2 py-1.5">
                            <div class="col-span-7 text-gray-800">{{ row.description }}</div>
                            <div class="col-span-2 text-center text-gray-600">{{ row.account_code }}</div>
                            <div class="col-span-3 text-right font-semibold text-gray-800">
                                <span v-if="receipt?.fee_type !== 'Waived'">
                                    ₱ {{ row.amount.toFixed(2) }}
                                </span>
                                <span v-else class="text-amber-600">Waived</span>
                            </div>
                        </div>

                        <!-- Blank filler rows (to match physical form look) -->
                        <div v-for="n in Math.max(0, 5 - collectionRows.length)" :key="`blank-${n}`"
                            class="grid grid-cols-12 border-b border-gray-100 text-xs px-2 py-1.5 h-7">
                            <div class="col-span-7"></div>
                            <div class="col-span-2"></div>
                            <div class="col-span-3 text-right text-gray-300">₱</div>
                        </div>
                    </div>

                    <!-- Total row -->
                    <div class="grid grid-cols-12 border-b border-gray-400 text-xs px-2 py-2">
                        <div class="col-span-9 font-bold text-gray-700 uppercase tracking-wider">TOTAL</div>
                        <div class="col-span-3 text-right font-bold text-gray-900">
                            <span v-if="receipt?.fee_type !== 'Waived'">₱ {{ totalAmount.toFixed(2) }}</span>
                            <span v-else class="text-amber-600">Waived</span>
                        </div>
                    </div>

                    <!-- Amount in words -->
                    <div class="px-3 py-2 border-b border-gray-400 text-xs">
                        <span class="text-gray-500">Amount in Words: </span>
                        <span class="font-semibold text-gray-800 italic">{{ amountInWords }}</span>
                    </div>

                    <!-- Waiver reason (shown only when waived) -->
                    <div v-if="receipt?.fee_type === 'Waived' && receipt?.waiver_reason"
                        class="px-3 py-2 border-b border-gray-400 text-xs bg-amber-50">
                        <span class="text-gray-500">Waiver Reason: </span>
                        <span class="font-semibold text-amber-700">{{ receipt.waiver_reason }}</span>
                    </div>

                    <!-- Payment method: Cash / Check / Money Order checkboxes -->
                    <div class="px-3 py-3 border-b border-gray-400 text-xs">
                        <div class="flex flex-col gap-1.5">
                            <label class="flex items-center gap-2">
                                <div class="w-4 h-4 border border-gray-600 rounded-sm flex items-center justify-center shrink-0"
                                    :class="receipt?.payment_method === 'Cash' ? 'bg-gray-800' : 'bg-white'">
                                    <svg v-if="receipt?.payment_method === 'Cash'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-gray-800">Cash</span>
                            </label>
                            <div class="flex items-start gap-2">
                                <div>
                                    <label class="flex items-center gap-2 mb-1">
                                        <div class="w-4 h-4 border border-gray-600 rounded-sm flex items-center justify-center shrink-0 bg-white"></div>
                                        <span class="font-semibold text-gray-800">Check</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <div class="w-4 h-4 border border-gray-600 rounded-sm flex items-center justify-center shrink-0 bg-white"></div>
                                        <span class="font-semibold text-gray-800">Money Order</span>
                                    </label>
                                </div>
                                <div class="ml-4 grid grid-cols-3 gap-x-4 text-gray-500 text-xs">
                                    <span class="font-semibold border-b border-gray-300 pb-0.5">Drawee Bank</span>
                                    <span class="font-semibold border-b border-gray-300 pb-0.5">Number</span>
                                    <span class="font-semibold border-b border-gray-300 pb-0.5">Date</span>
                                    <span class="pt-0.5 text-gray-300">—</span>
                                    <span class="pt-0.5 text-gray-300">—</span>
                                    <span class="pt-0.5 text-gray-300">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Received statement + Collecting Officer -->
                    <div class="px-3 py-3 border-b border-gray-400 text-xs">
                        <p class="text-gray-700 mb-4">Received the amount stated above.</p>
                        <div class="text-center mt-2">
                            <div class="border-t border-gray-600 pt-1 inline-block min-w-[180px]">
                                <p class="font-bold text-gray-800">Tourism Clerk</p>
                                <p class="text-gray-500">Collecting Officer</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes (if any) -->
                    <div v-if="receipt?.notes" class="px-3 py-2 border-b border-gray-400 text-xs bg-gray-50">
                        <span class="text-gray-500">Notes: </span>
                        <span class="text-gray-700">{{ receipt.notes }}</span>
                    </div>

                    <!-- Footer note -->
                    <div class="px-3 py-2 text-xs text-gray-500 italic">
                        NOTE: Write the number and date of this receipt on the back of check or money order received.
                    </div>
                </div>

                <!-- Action buttons (hidden on print) -->
                <div id="receipt-actions" class="flex justify-center gap-4 mt-6 print:hidden">
                    <button @click="printPage"
                        class="bg-gray-900 text-white font-bold py-2 px-6 rounded-lg hover:bg-gray-700 text-sm">
                        Print Receipt
                    </button>
                    <a :href="route('registration')"
                        class="bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-300 text-sm">
                        New Registration
                    </a>
                </div>

            </div>
        </div>
    </LandingLayout>
</template>

<style>
@media print {
    #receipt-actions { display: none !important; }
    body > *:not(#receipt-content) { display: none; }
}
</style>