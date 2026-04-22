<script setup>
/**
 * DestinationChecklist.vue
 *
 * Collapsible, searchable, scrollable checklist for selecting visitor destinations.
 * Used in both AdminRegPage and PublicRegPage (pre-registration).
 *
 * Props:
 *   modelValue  – Array<{ attraction_id: number|null, other_destination: string }>
 *   attractions – Array<{ id, name, type, sitio_name }>
 *
 * Emits: update:modelValue
 */
import { ref, computed } from 'vue'

const props = defineProps({
    modelValue:  { type: Array, default: () => [] },
    attractions: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])

// ── Panel state ───────────────────────────────────────────────────────────────
const isOpen   = ref(false)
const search   = ref('')

// ── Selection helpers ─────────────────────────────────────────────────────────
const selectedIds = computed(() =>
    new Set(
        props.modelValue
            .filter(d => d.attraction_id != null)
            .map(d => Number(d.attraction_id))
    )
)

const otherChecked = computed(() =>
    props.modelValue.some(d => d.attraction_id == null)
)

const otherText = computed(() =>
    props.modelValue.find(d => d.attraction_id == null)?.other_destination ?? ''
)

const selectedCount = computed(() => props.modelValue.length)

const toggleAttraction = (id) => {
    const numId = Number(id)
    let updated = [...props.modelValue]
    if (selectedIds.value.has(numId)) {
        updated = updated.filter(d => Number(d.attraction_id) !== numId)
    } else {
        updated.push({ attraction_id: numId, other_destination: '' })
    }
    emit('update:modelValue', updated)
}

const toggleOther = () => {
    let updated = [...props.modelValue]
    if (otherChecked.value) {
        updated = updated.filter(d => d.attraction_id != null)
    } else {
        updated.push({ attraction_id: null, other_destination: '' })
    }
    emit('update:modelValue', updated)
}

const updateOtherText = (text) => {
    const updated = props.modelValue.map(d =>
        d.attraction_id == null ? { ...d, other_destination: text } : d
    )
    emit('update:modelValue', updated)
}

// ── Filtered + grouped attractions ───────────────────────────────────────────
const filteredAttractions = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return props.attractions
    return props.attractions.filter(a =>
        a.name.toLowerCase().includes(q) ||
        (a.sitio_name && a.sitio_name.toLowerCase().includes(q)) ||
        (a.type && a.type.toLowerCase().includes(q))
    )
})

const groupedAttractions = computed(() => {
    const groups = {}
    for (const a of filteredAttractions.value) {
        const key = a.sitio_name || 'Other Locations'
        if (!groups[key]) groups[key] = []
        groups[key].push(a)
    }
    return Object.entries(groups).sort(([a], [b]) => a.localeCompare(b))
})

const noResults = computed(() =>
    search.value.trim() !== '' && filteredAttractions.value.length === 0
)

// ── Selection summary labels ──────────────────────────────────────────────────
const selectionSummary = computed(() => {
    const named = props.modelValue
        .filter(d => d.attraction_id != null)
        .map(d => props.attractions.find(a => a.id === d.attraction_id)?.name ?? `#${d.attraction_id}`)
    const otherEntry = props.modelValue.find(d => d.attraction_id == null)
    if (otherEntry) {
        named.push(otherEntry.other_destination
            ? `Other: ${otherEntry.other_destination}`
            : 'Other (unspecified)')
    }
    return named
})
</script>

<template>
    <div>
        <!-- ── Trigger button ──────────────────────────────────────────────── -->
        <!-- Distinct card that clearly stands apart from the white form bg    -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="w-full flex items-center justify-between px-4 py-3 rounded-xl border-2 transition-all duration-200 text-left"
            :class="isOpen
                ? 'border-blue-400 bg-blue-50 shadow-sm'
                : selectedCount > 0
                    ? 'border-blue-300 bg-blue-50/60 hover:border-blue-400'
                    : 'border-gray-300 bg-gray-50 hover:border-gray-400 hover:bg-white'"
        >
            <div class="flex items-center gap-2.5 min-w-0">
                <!-- Map pin icon -->
                <svg class="w-4 h-4 shrink-0 transition-colors"
                    :class="isOpen || selectedCount > 0 ? 'text-blue-500' : 'text-gray-400'"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>

                <!-- Label / summary -->
                <span v-if="selectedCount === 0"
                    class="text-sm font-medium text-gray-500">
                    Select destination(s) — optional
                </span>
                <span v-else class="text-sm font-semibold text-blue-700 truncate">
                    {{ selectedCount }} destination{{ selectedCount !== 1 ? 's' : '' }} selected
                </span>
            </div>

            <div class="flex items-center gap-2 ml-3 shrink-0">
                <!-- Count badge -->
                <span v-if="selectedCount > 0"
                    class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 bg-blue-500 text-white text-xs font-bold rounded-full">
                    {{ selectedCount }}
                </span>
                <!-- Chevron -->
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                    :class="isOpen ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <!-- ── Selection pills (shown outside panel when collapsed) ─────────── -->
        <div v-if="selectedCount > 0 && !isOpen" class="flex flex-wrap gap-1.5 mt-2">
            <span v-for="label in selectionSummary" :key="label"
                class="inline-flex items-center gap-1 bg-blue-100 border border-blue-200 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3"/>
                </svg>
                {{ label }}
            </span>
        </div>

        <!-- ── Expandable panel ───────────────────────────────────────────────
             Distinct from the white form background:
             — subtle shadow, border, and slightly off-white inner bg
        ──────────────────────────────────────────────────────────────────── -->
        <div v-show="isOpen"
            class="mt-1.5 rounded-xl border-2 border-blue-200 bg-white shadow-md overflow-hidden">

            <!-- No attractions state -->
            <div v-if="attractions.length === 0" class="px-4 py-6 text-center">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <p class="text-sm text-gray-400 font-medium">No attractions added yet</p>
                <p class="text-xs text-gray-400 mt-0.5">Ask admin to add attractions in the Attraction Management page.</p>
            </div>

            <template v-else>

                <!-- ── Search bar ─────────────────────────────────────────── -->
                <div class="px-3 pt-3 pb-2 border-b border-gray-100 bg-gray-50/60">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search destinations..."
                            class="w-full pl-8 pr-8 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent placeholder-gray-400"
                        />
                        <button v-if="search" @click="search = ''" type="button"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <!-- Result count hint -->
                    <p v-if="search" class="text-xs text-gray-400 mt-1.5 pl-1">
                        <template v-if="noResults">No results for "{{ search }}"</template>
                        <template v-else>{{ filteredAttractions.length }} result{{ filteredAttractions.length !== 1 ? 's' : '' }}</template>
                    </p>
                </div>

                <!-- ── Scrollable checklist (max-height = ~6 items visible) ─── -->
                <div class="overflow-y-auto" style="max-height: 260px;">

                    <!-- No search results -->
                    <div v-if="noResults" class="px-4 py-6 text-center">
                        <p class="text-sm text-gray-400">No destinations match "<span class="font-medium">{{ search }}</span>"</p>
                        <button type="button" @click="search = ''"
                            class="text-xs text-blue-500 hover:underline mt-1">Clear search</button>
                    </div>

                    <!-- Grouped attraction checkboxes -->
                    <template v-else>
                        <div v-for="([sitioName, items]) in groupedAttractions" :key="sitioName">

                            <!-- Sitio group header -->
                            <div class="sticky top-0 z-10 bg-gray-100/95 backdrop-blur-sm px-3 py-1.5 border-b border-gray-200/60">
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    {{ sitioName }}
                                </span>
                                <span class="ml-1.5 text-xs text-gray-400 font-normal normal-case">
                                    ({{ items.length }})
                                </span>
                            </div>

                            <!-- Checkboxes -->
                            <label v-for="a in items" :key="a.id"
                                class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-colors border-b border-gray-50 last:border-0"
                                :class="selectedIds.has(a.id)
                                    ? 'bg-blue-50 hover:bg-blue-100/70'
                                    : 'bg-white hover:bg-gray-50'">
                                <input
                                    type="checkbox"
                                    :checked="selectedIds.has(a.id)"
                                    @change="toggleAttraction(a.id)"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-400 cursor-pointer shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-medium text-gray-800 leading-tight">{{ a.name }}</span>
                                    <span class="ml-2 text-xs text-gray-400">{{ a.type }}</span>
                                </div>
                                <svg v-if="selectedIds.has(a.id)"
                                    class="w-4 h-4 text-blue-500 shrink-0"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                                </svg>
                            </label>

                        </div>
                    </template>

                    <!-- ── "Other" option — always shown at the bottom ─────── -->
                    <div v-if="!noResults" class="border-t-2 border-dashed border-gray-200 bg-amber-50/30">
                        <label class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-colors"
                            :class="otherChecked ? 'bg-amber-50' : 'hover:bg-amber-50/60'">
                            <input
                                type="checkbox"
                                :checked="otherChecked"
                                @change="toggleOther"
                                class="rounded border-gray-300 text-amber-500 focus:ring-amber-400 cursor-pointer shrink-0"
                            />
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-gray-700">Other</span>
                                <span class="ml-2 text-xs text-gray-400">not in the list above</span>
                            </div>
                        </label>
                        <!-- "Other" text input -->
                        <div v-if="otherChecked" class="px-4 pb-3 pt-0.5">
                            <input
                                :value="otherText"
                                @input="updateOtherText($event.target.value)"
                                type="text"
                                placeholder="Please specify the destination..."
                                class="w-full border border-amber-300 rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300 bg-white placeholder-amber-300/70"
                            />
                            <p class="text-xs text-amber-600 mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                This will be flagged for review in the Attraction Management page.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- ── Panel footer — clear all + close ───────────────────── -->
                <div class="flex items-center justify-between px-3 py-2 border-t border-gray-100 bg-gray-50/60">
                    <button v-if="selectedCount > 0" type="button"
                        @click="emit('update:modelValue', [])"
                        class="text-xs text-red-400 hover:text-red-600 font-medium transition-colors">
                        Clear all
                    </button>
                    <span v-else class="text-xs text-gray-400">
                        {{ attractions.length }} destination{{ attractions.length !== 1 ? 's' : '' }} available
                    </span>

                    <button type="button" @click="isOpen = false"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-1">
                        Done
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                </div>

            </template>
        </div>
    </div>
</template>