<template>
  <div class="h-screen flex flex-col">

    <div class="relative w-full h-[550px]">
      <PanoramaViewer
        :src="mainImage"
        class="w-full h-full"
      />

      <div class="absolute top-0 right-4 flex p-4 pointer-events-none mt-4">
        <h1 class="text-xl text-white font-normal tracking-wide "
            style="font-family: 'Chewy', cursive; ">
          {{ location?.name || 'Location Not Found' }}
        </h1>
      </div>
      
    </div>

  </div>
</template>

<script setup>
import PanoramaViewer from '@/Pages/VirtualTour/PanoramaViewer.vue'
import { locations } from '@/data/locations'
import { useRoute } from 'vue-router'
import { computed } from 'vue'

const route = useRoute()

const location = computed(() =>
  locations.find(loc => String(loc.id) === String(route.params.id))
)

const currentView = computed(() => route.query.view || 'aerial')

const mainImage = computed(() => location.value?.images?.[currentView.value])
</script>