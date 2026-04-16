<template>
  <div ref="viewerEl" class="panorama-fullscreen"></div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { Viewer } from '@photo-sphere-viewer/core'
import '@photo-sphere-viewer/core/index.css'

const props = defineProps({
  src: { type: String, required: true }
})

const viewerEl = ref(null)
let viewer = null

onMounted(() => {
  viewer = new Viewer({
    container: viewerEl.value,
    panorama: props.src,
    navbar: ['zoom', 'fullscreen'],
    defaultZoomLvl: 0,
  })
})

watch(() => props.src, (newSrc) => {
  viewer?.setPanorama(newSrc)
})

onBeforeUnmount(() => {
  viewer?.destroy()
})
</script>

<style scoped>
.panorama-fullscreen {
  position: fixed;   
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 0;    
}
.panorama-fullscreen :deep(.psv-navbar) {
  left: 50%;
  transform: translateX(-50%);
  width: auto;
  border-radius: 999px;
  bottom: 24px;
}
</style>