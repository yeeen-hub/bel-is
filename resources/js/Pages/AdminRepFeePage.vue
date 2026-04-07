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

        <p class="text-xs text-gray-500 mt-5 mb-5"> Reports / Fee Revenue </p>

        <h1 class="font-heading text-gray-800 font-semibold text-2xl"> Dashboard </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 p-4 gap-4 mt-4">
 
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <h2 class="text-gray-800 font-medium text-sm"> Total Revenue </h2>
                        <p class="text-2xl font-bold text-gray-800"> 24, 900 php </p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <h2 class="text-gray-800 font-medium text-sm"> Avereage Daily Revenue </h2>
                        <p class="text-2xl font-bold text-gray-800"> 803 php </p>
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

                    <button class="h-10 bg-gray-900 text-white font-bold px-4 text-sm rounded">
                    Enter
                    </button>

                </div>

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
                    <th class="p-2 text-black">Visit Category</th>
                    <th class="p-2 text-black ">Name</th>
                    <th class="p-2 text-black ">Revenue</th>
                </tr>
            </thead>

            <tbody>
                <tr @click="showModal = true" class="cursor-pointer hover:bg-gray-100">
                    <td class="p-2 border-b">Regular</td>
                    <td class="p-2 border-b">John Doe</td>
                    <td class="p-2 border-b"></td>
                </tr>
            </tbody>
        </table>

       
    </LandingLayout>
</template>

<script>
import LandingLayout from '@/Layouts/SidebarLayout.vue';

export default {
  components: { LandingLayout }
}
</script>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue';

const ddreports = ref('');
const openDdreports = ref(false);
const dropdownRef = ref(null);
const brgyRef = ref(null);
const ddbrgy = ref('')
const openDdbrgy = ref(false)


const selectBrgy = (val) => {
  ddbrgy.value = val
  openDdbrgy.value = false
}

const selectbgryOptions = [
  'Hinugtan', 'Bel-is Cove'
];


onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!dropdownRef.value?.contains(e.target)) {
      openDdreports.value = false;
    }
    
    if (!brgyRef.value?.contains(e.target)) {
      openDdbrgy.value = false
    }

  });
});


</script>