<script setup>
/**
 * ExportModal.vue — Reusable export modal for all report pages.
 *
 * Props:
 *   show          – Boolean
 *   reportType    – 'analytics' | 'demographics' | 'fee-revenue'
 *   defaultTitle  – Pre-filled report title
 *   columnDefs    – Array<{ key, label }> all available columns
 *   filteredRows  – Rows currently on screen (server-filtered)
 *   allRows       – All rows from page load (unfiltered)
 *   sitios        – Array<{ id, name }> for area filter dropdown
 *   attractions   – Array<{ id, name }> for analytics attraction filter
 */
import { ref, computed, watch } from 'vue'

const props = defineProps({
    show:         { type: Boolean, default: false },
    reportType:   { type: String,  required: true },
    defaultTitle: { type: String,  default: 'Report' },
    columnDefs:   { type: Array,   default: () => [] },
    filteredRows: { type: Array,   default: () => [] },
    allRows:      { type: Array,   default: () => [] },
    sitios:       { type: Array,   default: () => [] },
    attractions:  { type: Array,   default: () => [] },
})

const emit = defineEmits(['close'])

// ── Form state ────────────────────────────────────────────────────────────────
const title         = ref('')
const subtitle      = ref('')
const notes         = ref('')
const scope         = ref('filtered')
const activeColKeys = ref([])
const exporting     = ref(false)
const exportType    = ref('')

// ── In-modal filter state ─────────────────────────────────────────────────────
const modalSearch       = ref('')
const modalPurpose      = ref('')
const modalArea         = ref('')
const modalAttractionId = ref('')
const modalCategory     = ref('')
const modalFeeType      = ref('')
const modalDateFrom     = ref('')
const modalDateTo       = ref('')

// ── Reset when modal opens ────────────────────────────────────────────────────
watch(() => props.show, (val) => {
    if (val) {
        title.value         = props.defaultTitle
        subtitle.value      = ''
        notes.value         = ''
        scope.value         = 'filtered'
        activeColKeys.value = props.columnDefs.map(c => c.key)
        exporting.value     = false
        exportType.value    = ''
        // Reset modal filters
        modalSearch.value       = ''
        modalPurpose.value      = ''
        modalArea.value         = ''
        modalAttractionId.value = ''
        modalCategory.value     = ''
        modalFeeType.value      = ''
        modalDateFrom.value     = ''
        modalDateTo.value       = ''
    }
})

// ── Active columns ────────────────────────────────────────────────────────────
const activeColumns = computed(() =>
    props.columnDefs.filter(c => activeColKeys.value.includes(c.key))
)

const toggleCol = (key) => {
    const idx = activeColKeys.value.indexOf(key)
    if (idx === -1) {
        activeColKeys.value = props.columnDefs
            .map(c => c.key)
            .filter(k => activeColKeys.value.includes(k) || k === key)
    } else {
        if (activeColKeys.value.length === 1) return // keep at least one
        activeColKeys.value = activeColKeys.value.filter(k => k !== key)
    }
}

// ── Client-side filter applied to allRows when scope = 'filtered' ─────────────
// Each report type filters on its own relevant fields.
// The _raw date fields (arrival_at_raw, collected_at_raw) are hidden from
// column display but present in allRows for filtering.
const clientFilteredRows = computed(() => {
    if (scope.value === 'all') return props.allRows

    return props.allRows.filter(row => {
        // ── Search ──────────────────────────────────────────────────────────
        if (modalSearch.value) {
            const q = modalSearch.value.toLowerCase()
            const searchable = [
                row.full_name, row.place_of_origin, row.purpose,
                row.destinations, row.visit_category,
            ].filter(Boolean).join(' ').toLowerCase()
            if (!searchable.includes(q)) return false
        }

        // ── Analytics filters ───────────────────────────────────────────────
        if (props.reportType === 'analytics') {
            if (modalPurpose.value && row.purpose !== modalPurpose.value) return false
            if (modalArea.value) {
                // area filter: check if destinations string contains sitio name
                // (allRows destinations already resolved to attraction names)
                // For a strict match we'd need sitio_name in the row — approximate with string include
                if (!(row.destinations ?? '').toLowerCase().includes(modalArea.value.toLowerCase())) return false
            }
            if (modalAttractionId.value) {
                const attrName = props.attractions.find(a => a.id == modalAttractionId.value)?.name ?? ''
                if (attrName && !(row.destinations ?? '').toLowerCase().includes(attrName.toLowerCase())) return false
            }
        }

        // ── Demographics filters ────────────────────────────────────────────
        if (props.reportType === 'demographics') {
            if (modalSearch.value) {
                if (!(row.place_of_origin ?? '').toLowerCase().includes(modalSearch.value.toLowerCase())) return false
            }
        }

        // ── Fee Revenue filters ─────────────────────────────────────────────
        if (props.reportType === 'fee-revenue') {
            if (modalCategory.value && row.visit_category !== modalCategory.value) return false
            if (modalFeeType.value  && row.fee_type       !== modalFeeType.value)  return false
        }

        // ── Date range (shared) ─────────────────────────────────────────────
        const dateRaw = row.arrival_at_raw || row.collected_at_raw || ''
        if (modalDateFrom.value && dateRaw && dateRaw < modalDateFrom.value) return false
        if (modalDateTo.value   && dateRaw && dateRaw > modalDateTo.value)   return false

        return true
    })
})

// ── Rows shown in preview and sent to export ──────────────────────────────────
const exportRows = computed(() => clientFilteredRows.value)

const scopeLabel = computed(() => {
    const count = exportRows.value.length
    return scope.value === 'all'
        ? `All Data (${count} records)`
        : `Filtered Results (${count} records)`
})

// ── Route map ─────────────────────────────────────────────────────────────────
const routeMap = {
    'analytics':   { pdf: 'reports.analytics.export.pdf',    excel: 'reports.analytics.export.excel'    },
    'demographics':{ pdf: 'reports.demographics.export.pdf', excel: 'reports.demographics.export.excel' },
    'fee-revenue': { pdf: 'reports.fee-revenue.export.pdf',  excel: 'reports.fee-revenue.export.excel'  },
}

// ── Export via axios blob ─────────────────────────────────────────────────────
const doExport = async (type) => {
    exportType.value = type
    exporting.value  = true

    const routeName = routeMap[props.reportType]?.[type]
    if (!routeName) { exporting.value = false; return }

    const payload = {
        title:       title.value,
        subtitle:    subtitle.value,
        notes:       notes.value,
        scope_label: scopeLabel.value,
        columns:     activeColumns.value,
        rows:        exportRows.value,
    }

    try {
        const response = await axios.post(route(routeName), {
            payload: JSON.stringify(payload),
        }, { responseType: 'blob' })

        const disposition   = response.headers['content-disposition'] ?? ''
        const filenameChunk = disposition.split('filename=')[1] ?? ''
        const filename      = filenameChunk
            ? filenameChunk.split(';')[0].replace(/"/g, '').trim()
            : `${props.reportType}-report.${type === 'pdf' ? 'pdf' : 'xlsx'}`

        const blob = new Blob([response.data], { type: response.headers['content-type'] })
        const url  = URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href     = url
        link.download = filename
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        URL.revokeObjectURL(url)

    } catch (err) {
        console.error('Export failed:', err)
        alert('Export failed. Please try again.')
    } finally {
        exporting.value  = false
        exportType.value = ''
    }
}

const close = () => { if (!exporting.value) emit('close') }
</script>

<template>
    <Teleport to="body">
        <div v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @click.self="close">

            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close" />

            <!-- Modal -->
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col overflow-hidden">

                <!-- ── Modal Header ── -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
                    <div>
                        <h2 class="text-base font-bold text-gray-800">Export Report</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Customize then download as PDF or Excel</p>
                    </div>
                    <button @click="close" class="text-gray-400 hover:text-gray-600 transition text-xl font-bold">×</button>
                </div>

                <!-- ── Modal Body: 2-column layout ── -->
                <div class="flex flex-1 overflow-hidden">

                    <!-- ── LEFT: Edit Panel ── -->
                    <div class="w-80 shrink-0 border-r border-gray-200 overflow-y-auto p-5 space-y-5 bg-gray-50/40">

                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Report Title</label>
                            <input v-model="title" type="text"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        </div>

                        <!-- Subtitle -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Subtitle <span class="font-normal text-gray-400">(optional)</span>
                            </label>
                            <input v-model="subtitle" type="text" placeholder="e.g. April 2026 Summary"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                        </div>

                        <!-- Data Scope -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Data Scope</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="radio" v-model="scope" value="filtered"
                                        class="text-gray-800 focus:ring-gray-400" />
                                    <span class="text-sm text-gray-700">
                                        Filtered results
                                        <span class="text-xs text-gray-400 ml-1">({{ exportRows.length }} rows)</span>
                                    </span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="radio" v-model="scope" value="all"
                                        class="text-gray-800 focus:ring-gray-400" />
                                    <span class="text-sm text-gray-700">
                                        All data
                                        <span class="text-xs text-gray-400 ml-1">({{ allRows.length }} rows)</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- ── In-modal filters (shown only when Filtered results selected) ── -->
                        <div v-if="scope === 'filtered'" class="space-y-3 border-t border-gray-200 pt-4">
                            <p class="text-xs font-bold text-gray-700">Filters</p>

                            <!-- Search — all reports -->
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Search</label>
                                <input v-model="modalSearch" type="text" placeholder="Search..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400" />
                            </div>

                            <!-- Analytics-specific filters -->
                            <template v-if="reportType === 'analytics'">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Purpose</label>
                                    <select v-model="modalPurpose"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        <option value="">All Purposes</option>
                                        <option value="Tourism">Tourism</option>
                                        <option value="Research">Research</option>
                                        <option value="Event">Event</option>
                                        <option value="Official Visit">Official Visit</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div v-if="sitios.length">
                                    <label class="block text-xs text-gray-500 mb-1">Area (Sitio)</label>
                                    <select v-model="modalArea"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        <option value="">All Areas</option>
                                        <option v-for="s in sitios" :key="s.id" :value="s.name">{{ s.name }}</option>
                                    </select>
                                </div>
                                <div v-if="attractions.length">
                                    <label class="block text-xs text-gray-500 mb-1">Attraction</label>
                                    <select v-model="modalAttractionId"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        <option value="">All Attractions</option>
                                        <option v-for="a in attractions" :key="a.id" :value="a.id">{{ a.name }}</option>
                                    </select>
                                </div>
                            </template>

                            <!-- Demographics-specific filters -->
                            <template v-if="reportType === 'demographics'">
                                <div v-if="sitios.length">
                                    <label class="block text-xs text-gray-500 mb-1">Area (Sitio)</label>
                                    <select v-model="modalArea"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        <option value="">All Areas</option>
                                        <option v-for="s in sitios" :key="s.id" :value="s.name">{{ s.name }}</option>
                                    </select>
                                </div>
                            </template>

                            <!-- Fee Revenue-specific filters -->
                            <template v-if="reportType === 'fee-revenue'">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Visit Category</label>
                                    <select v-model="modalCategory"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        <option value="">All Categories</option>
                                        <option value="Adult">Adult</option>
                                        <option value="Child">Child</option>
                                        <option value="Senior Citizen">Senior Citizen</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Fee Status</label>
                                    <select v-model="modalFeeType"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        <option value="">All Statuses</option>
                                        <option value="Standard">Collected (Standard)</option>
                                        <option value="Waived">Waived</option>
                                    </select>
                                </div>
                                <div v-if="sitios.length">
                                    <label class="block text-xs text-gray-500 mb-1">Area (Sitio)</label>
                                    <select v-model="modalArea"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        <option value="">All Areas</option>
                                        <option v-for="s in sitios" :key="s.id" :value="s.name">{{ s.name }}</option>
                                    </select>
                                </div>
                            </template>

                            <!-- Date range — all reports -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Date From</label>
                                    <input v-model="modalDateFrom" type="date"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-gray-400" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Date To</label>
                                    <input v-model="modalDateTo" type="date"
                                        class="w-full border border-gray-300 rounded-lg px-2 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-gray-400" />
                                </div>
                            </div>

                            <!-- Clear filters -->
                            <button v-if="modalSearch || modalPurpose || modalArea || modalAttractionId || modalCategory || modalFeeType || modalDateFrom || modalDateTo"
                                type="button"
                                @click="modalSearch = modalPurpose = modalArea = modalAttractionId = modalCategory = modalFeeType = modalDateFrom = modalDateTo = ''"
                                class="text-xs text-red-500 hover:text-red-700 font-medium transition">
                                ✕ Clear all filters
                            </button>
                        </div>

                        <!-- Columns -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Columns</label>
                            <div class="space-y-1.5">
                                <label v-for="col in columnDefs" :key="col.key"
                                    class="flex items-center gap-2.5 cursor-pointer group">
                                    <input type="checkbox"
                                        :checked="activeColKeys.includes(col.key)"
                                        @change="toggleCol(col.key)"
                                        :disabled="activeColKeys.includes(col.key) && activeColKeys.length === 1"
                                        class="rounded border-gray-300 text-gray-800 focus:ring-gray-400 cursor-pointer" />
                                    <span class="text-sm text-gray-700 group-hover:text-gray-900 transition">
                                        {{ col.label }}
                                    </span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">At least one column must be selected.</p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Notes / Remarks <span class="font-normal text-gray-400">(optional)</span>
                            </label>
                            <textarea v-model="notes" rows="4"
                                placeholder="Add any notes or remarks to appear at the bottom of the export..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 resize-none" />
                        </div>

                    </div>

                    <!-- ── RIGHT: Live Preview ── -->
                    <div class="flex-1 overflow-auto p-5">

                        <!-- Preview header (simulates PDF output) -->
                        <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-white">

                            <!-- Official header band -->
                            <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200 bg-gray-50">
                                <img src="/images/brgylogo.png" alt="Logo"
                                    class="w-10 h-10 rounded-full object-cover border border-gray-200 shrink-0"
                                    onerror="this.style.display='none'" />
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Barangay Bel-is, Buruanga, Aklan</p>
                                    <p class="text-xs text-gray-500">Local Government Unit — Tourism Management System</p>
                                    <p class="text-sm font-bold text-gray-900 mt-1">
                                        {{ title || 'Report Title' }}
                                        <span v-if="subtitle" class="font-normal text-gray-500 text-xs ml-1">— {{ subtitle }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Scope + count -->
                            <div class="flex items-center justify-between px-5 py-2 bg-gray-50/50 border-b border-gray-100">
                                <span class="text-xs text-gray-500 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded-full">
                                    {{ scopeLabel }}
                                </span>
                                <span class="text-xs text-gray-400">{{ exportRows.length }} record(s)</span>
                            </div>

                            <!-- Preview table -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-gray-900 text-white">
                                        <tr>
                                            <th class="px-3 py-2 font-semibold w-8">#</th>
                                            <th v-for="col in activeColumns" :key="col.key"
                                                class="px-3 py-2 font-semibold whitespace-nowrap">
                                                {{ col.label }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Show max 10 rows in preview -->
                                        <tr v-for="(row, i) in exportRows.slice(0, 10)" :key="i"
                                            :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                                            <td class="px-3 py-2 text-gray-400">{{ i + 1 }}</td>
                                            <td v-for="col in activeColumns" :key="col.key"
                                                class="px-3 py-2 text-gray-700 max-w-[160px] truncate">
                                                {{ row[col.key] ?? '—' }}
                                            </td>
                                        </tr>
                                        <tr v-if="exportRows.length === 0">
                                            <td :colspan="activeColumns.length + 1"
                                                class="px-3 py-8 text-center text-gray-400">
                                                No data to preview.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Preview truncation notice -->
                            <div v-if="exportRows.length > 10"
                                class="px-5 py-2 bg-amber-50 border-t border-amber-100 text-xs text-amber-700 text-center">
                                Preview shows first 10 of {{ exportRows.length }} rows. All rows will be included in the export.
                            </div>

                            <!-- Notes preview -->
                            <div v-if="notes" class="px-5 py-3 border-t border-gray-200">
                                <p class="text-xs font-bold text-gray-600 mb-1">Notes / Remarks</p>
                                <p class="text-xs text-gray-500 whitespace-pre-wrap">{{ notes }}</p>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- ── Modal Footer ── -->
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50 shrink-0">
                    <p class="text-xs text-gray-400">
                        {{ activeColumns.length }} column(s) selected · {{ exportRows.length }} row(s)
                    </p>
                    <div class="flex items-center gap-3">
                        <button @click="close" :disabled="exporting"
                            class="text-sm text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-100 transition disabled:opacity-50">
                            Cancel
                        </button>
                        <button @click="doExport('excel')" :disabled="exporting || activeColumns.length === 0"
                            class="flex items-center gap-2 text-sm font-bold px-4 py-2 rounded-xl border border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white transition disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ exporting && exportType === 'excel' ? 'Exporting...' : 'Export Excel' }}
                        </button>
                        <button @click="doExport('pdf')" :disabled="exporting || activeColumns.length === 0"
                            class="flex items-center gap-2 text-sm font-bold px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black transition disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ exporting && exportType === 'pdf' ? 'Exporting...' : 'Export PDF' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </Teleport>
</template>