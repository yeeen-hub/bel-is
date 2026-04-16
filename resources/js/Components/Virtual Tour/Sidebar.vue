// app/resources/js/Components/Virtual Tour/
<template>
  <div class="fixed left-0 top-0 z-50 flex h-screen">

    <aside
      :class="isOpen ? 'w-64' : 'w-0'"
      class="relative h-screen text-white flex flex-col overflow-hidden transition-all duration-300"
    >
      <img
        :src="sidebarBg"
        class="absolute inset-0 w-full h-full object-cover transition-all duration-500"
      />
      <div class="absolute inset-0"></div>

      <div class="relative z-10 flex flex-col h-full p-4 w-64">

        <router-link
          to="/VTHome"
          class="inline-flex items-center gap-3 bg-transparent border-none cursor-pointer p-2 mt-2 mb-2 ml-2"
        >
          <i class="fa-solid fa-chevron-left text-white"></i>
          <span class="text-white text-base tracking-wide" style="font-family: 'Chewy', cursive;">
            Back to Map
          </span>
        </router-link>

        <div class="flex justify-center mb-2">
          <img src="../../../assets/text.png" class="w-[550px] h-auto object-contain" />
        </div>

        <nav class="flex flex-col gap-3">
          <button
              @click="$router.push({ query: { view: 'aerial' } })"
              :class="currentView === 'aerial' ? 'bg-orange-500' : ''"
              class="sidebar-btn flex items-center gap-4 px-4 py-1 border-4 border-white rounded-xl cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300"
            >
            <i class="fa-solid fa-arrows-to-eye"></i>
            <span style="font-family: 'Chewy', cursive;" class="text-lg">Aerial View</span>
          </button>
           <button
              @click="$router.push({ query: { view: 'beach' } })"
              :class="currentView === 'beach' ? 'bg-orange-500' : ''"
              class="sidebar-btn flex items-center gap-4 px-4 py-1 border-4 border-white rounded-xl cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300"
            >
            <i class="fa-solid fa-umbrella-beach"></i>
            <span style="font-family: 'Chewy', cursive;" class="text-lg">Beach Area</span>
          </button>
           <button
              @click="$router.push({ query: { view: 'pool' } })"
              :class="currentView === 'pool' ? 'bg-orange-500' : ''"
              class="sidebar-btn flex items-center gap-4 px-4 py-1 border-4 border-white rounded-xl cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300"
            >
            <i class="fa-solid fa-person-swimming"></i>
            <span style="font-family: 'Chewy', cursive;" class="text-lg">Pool Area</span>
          </button>
           <button
              @click="$router.push({ query: { view: 'hallway' } })"
              :class="currentView === 'hallway' ? 'bg-orange-500' : ''"
              class="sidebar-btn flex items-center gap-4 px-4 py-1 border-4 border-white rounded-xl cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300"
            >
            <i class="fa-solid fa-archway text-sm"></i>
            <span style="font-family: 'Chewy', cursive;" class="text-lg">Hallway</span>
          </button>
          <button
              @click="$router.push({ query: { view: 'lobby' } })"
              :class="currentView === 'lobby' ? 'bg-orange-500' : ''"
              class="sidebar-btn flex items-center gap-4 px-4 py-1 border-4 border-white rounded-xl cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300"
            >
            <i class="fa-solid fa-bell-concierge"></i>
            <span style="font-family: 'Chewy', cursive;" class="text-lg">Lobby</span>
          </button>
           <button
              @click="$router.push({ query: { view: 'room' } })"
              :class="currentView === 'room' ? 'bg-orange-500' : ''"
              class="sidebar-btn flex items-center gap-4 px-4 py-1 border-4 border-white rounded-xl cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300"
            >
            <i class="fa fa-bed"></i>
            <span style="font-family: 'Chewy', cursive;" class="text-lg">Room</span>
          </button>
           <button
              @click="$router.push({ query: { view: 'restaurant' } })"
              :class="currentView === 'restaurant' ? 'bg-orange-500' : ''"
              class="sidebar-btn flex items-center gap-4 px-4 py-1 border-4 border-white rounded-xl cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300"
            >
            <i class="fas fa-utensils"></i>
            <span style="font-family: 'Chewy', cursive;" class="text-lg">Restaurant</span>
          </button>
        </nav>

      </div>
    </aside>

    <button
      @click="isOpen = !isOpen"
      class="absolute right-0 translate-x-full top-1/2 -translate-y-1/2 w-5 h-14 rounded-r-lg border-none cursor-pointer flex items-center justify-center z-50"
      :style="{ backgroundImage: `url(${sidebarBg})`, backgroundSize: 'cover' }"
    >
      <i
        :class="isOpen ? 'fa-solid fa-chevron-left' : 'fa-solid fa-chevron-right'"
        class="text-white text-xs"
      ></i>
    </button>

    <div
      v-if="!isOpen"
      class="flex flex-col items-center gap-3 py-4 px-1 h-screen"
      :style="{
        backgroundImage: `url(${sidebarBg})`,
        backgroundRepeat: 'no-repeat',
        backgroundPosition: 'center',
        backgroundSize: 'cover'
      }"
    >
      <router-link
        to="/VTHome"
        class="flex items-center justify-center w-9 h-9 border-none cursor-pointer text-white mb-1 mt-2">
        <i class="fa-solid fa-chevron-left text-sm"></i>
      </router-link>

      <div class="flex justify-center mb-1 mt-4">
          <img src="../../../assets/text.png" class="w-[55px] h-auto object-contain" />
        </div>

      <nav class="flex flex-col gap-4 mt-8">
          <button @click="$router.push({ query: { view: 'aerial' } })" 
            :style="currentView === 'aerial' ? { backgroundColor: 'rgb(249 115 22)' } : {}"
            class="sidebar-btn flex items-center justify-center gap-4 px-2 py-1 border-2 border-white rounded-lg cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300">
            <i class="fa-solid fa-arrows-to-eye text-sm"></i>
          </button>

          <button @click="$router.push({ query: { view: 'beach' } })" 
            :style="currentView === 'beach' ? { backgroundColor: 'rgb(249 115 22)' } : {}"
            class="sidebar-btn flex items-center justify-center gap-4 px-2 py-1 border-2 border-white rounded-lg cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300">
            <i class="fa-solid fa-umbrella-beach text-sm"></i>
          </button>

          <button @click="$router.push({ query: { view: 'pool' } })" 
            :style="currentView === 'pool' ? { backgroundColor: 'rgb(249 115 22)' } : {}"
            class="sidebar-btn flex items-center justify-center gap-4 px-2 py-1 border-2 border-white rounded-lg cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300">
            <i class="fa-solid fa-person-swimming text-sm"></i>
          </button>

          <button @click="$router.push({ query: { view: 'hallway' } })" 
            :style="currentView === 'hallway' ? { backgroundColor: 'rgb(249 115 22)' } : {}"
            class="sidebar-btn flex items-center justify-center gap-4 px-2 py-1 border-2 border-white rounded-lg cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300">
            <i class="fa-solid fa-archway text-sm"></i>
          </button>

          <button @click="$router.push({ query: { view: 'lobby' } })" 
            :style="currentView === 'lobby' ? { backgroundColor: 'rgb(249 115 22)' } : {}"
            class="sidebar-btn flex items-center justify-center gap-4 px-2 py-1 border-2 border-white rounded-lg cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300">
            <i class="fa-solid fa-bell-concierge text-sm"></i>
          </button>

          <button @click="$router.push({ query: { view: 'room' } })" 
            :style="currentView === 'room' ? { backgroundColor: 'rgb(249 115 22)' } : {}"
            class="sidebar-btn flex items-center justify-center gap-4 px-2 py-1 border-2 border-white rounded-lg cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300">
            <i class="fa fa-bed text-sm"></i>
          </button>

          <button @click="$router.push({ query: { view: 'restaurant' } })" 
            :style="currentView === 'restaurant' ? { backgroundColor: 'rgb(249 115 22)' } : {}"
            class="sidebar-btn flex items-center justify-center gap-4 px-2 py-1 border-2 border-white rounded-lg cursor-pointer ml-2 mr-2 text-white hover:bg-orange-500 transition-colors duration-300">
            <i class="fas fa-utensils text-sm"></i>
          </button>
        </nav>
    </div>

  </div>
</template>

<script setup>
import sidebarBg from '../../../assets/sidebarbg.png'
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { locations } from '@/data/locations'

const route = useRoute() 

const isOpen = ref(true)

const currentView = computed(() => {
  return route.query.view || 'aerial'
})

const location = computed(() => {
  return locations.find(loc =>
    String(loc.id) === String(route.params.id)
  )
})

const sidebarImage = computed(() => {
  return location.value?.images?.[currentView.value] || sidebarBg
})


</script>