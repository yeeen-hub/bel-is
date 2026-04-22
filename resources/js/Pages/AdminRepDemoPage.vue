<template>
    <LandingLayout>
        <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
                <div class="relative flex-1">
                    <input type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
                </div> 
                <FontAwesomeIcon icon="bell" />
                <FontAwesomeIcon icon="user" />
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-5 mb-5"> Reports / Analytics </p>

        <div class="relative w-1/5" ref="dropdownRef">
            <button
                type="button"
                @click="openDdreports = !openDdreports"
                class="w-full border py-2 px-3 rounded text-left"
            >
                {{ ddreports || 'Demographics' }}
            </button>

            <div v-if="openDdreports"
                class="absolute left-0 w-full mt-1 bg-white border rounded shadow z-10">
                
               <Link
                v-for="option in purposeOptions"
                :key="option.label"
                :href="option.link"
                class="block px-3 py-2 hover:bg-gray-100"
                >
                {{ option.label }}
                </Link>
            </div>
        </div>

        <div class="mt-5">
            <label class="block text-gray-500 text-sm mb-2">Area</label>

            <div class="flex items-center justify-between w-full">

                <div class="flex items-center gap-3">

                    <div class="relative w-48" ref="brgyRef">
                    <button
                        type="button"
                        @click.stop="openDdbrgy = !openDdbrgy"
                        class="w-full h-10 border rounded px-3 text-left flex items-center"
                    >
                        {{ ddbrgy || 'Select area' }}
                    </button>

                    <div v-if="openDdbrgy"
                        class="absolute left-0 w-full mt-1 bg-white border rounded shadow z-10">
                        
                        <div
                        v-for="option in selectbgryOptions"
                        :key="option"
                        @click="selectBrgy(option)"
                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                        >
                        {{ option }}
                        </div>
                    </div>
                    </div>

                    <div class="flex items-center h-10 border rounded px-3 gap-2 w-72">
                    <input 
                        type="date" 
                        v-model="startDate"
                        class="border-0 border-0 text-sm outline-none bg-transparent w-full focus:ring-0 focus:outline-none"
                    />

                    <span class="text-gray-400">|</span>

                    <input
                        type="date" 
                        v-model="endDate"
                        class="border-0 border-0 text-sm outline-none bg-transparent w-full"
                    />
                    </div>

                    <!-- ENTER BUTTON -->
                    <button @click="applyFilters" class="h-10 bg-gray-900 text-white font-bold px-4 text-sm rounded">
                    Enter
                    </button>

                </div>

                <!-- RIGHT SIDE -->
                <div class="flex items-center gap-2">
                    <button class="h-10 border border-gray-900 font-bold px-3 text-sm rounded-lg">
                    Export PDF
                    </button>

                    <button class="h-10 border border-gray-900 font-bold px-3 text-sm rounded-lg">
                    Export EXCEL
                    </button>
                </div>
            </div>
        </div>

        <table class="w-full text-left mt-5 border-collapse text-center bg-white rounded-lg shadow-md ">
            <thead class="bg-gray-200 ">
                <tr>
                    <th class="p-2 text-black">Origin</th>
                    <th class="p-2 text-black ">Total Tourist</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(row, i) in rows" :key="i"
                    class="cursor-pointer hover:bg-gray-100">
                    <td class="p-2 border-b">{{ row.place_of_origin }}</td>
                    <td class="p-2 border-b">{{ row.total_tourists }}</td>
                </tr>

                <tr v-if="rows.length === 0">
                    <td colspan="2" class="p-4 text-center text-gray-400 text-sm">No data found.</td>
                </tr>
            </tbody>
        </table>

    </LandingLayout>
</template>

<script>
import LandingLayout from '@/Layouts/SidebarLayout.vue';
export default { components: { LandingLayout } }
</script>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps({
    rows:    { type: Array,  default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

const ddreports     = ref('')
const openDdreports = ref(false)
const dropdownRef   = ref(null)
const brgyRef       = ref(null)
const ddbrgy        = ref(props.filters.area      ?? '')
const openDdbrgy    = ref(false)
const startDate     = ref(props.filters.date_from ?? '')
const endDate       = ref(props.filters.date_to   ?? '')

const purposeOptions    = [{ label: 'Overview', link: route('reports.analytics') }]
const selectbgryOptions = ['Hinugtan', 'Bel-is Cove']

const selectBrgy = (val) => { ddbrgy.value = val; openDdbrgy.value = false }

const applyFilters = () => {
    router.get(route('reports.demographics'), {
        date_from: startDate.value || undefined,
        date_to:   endDate.value   || undefined,
        area:      ddbrgy.value    || undefined,
    }, { preserveState: true, replace: true })
}

onMounted(() => {
    document.addEventListener('click', (e) => {
        if (!dropdownRef.value?.contains(e.target)) openDdreports.value = false
        if (!brgyRef.value?.contains(e.target))     openDdbrgy.value    = false
    })
})
</script>