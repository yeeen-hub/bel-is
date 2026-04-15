<template>
  <div class="flex h-screen">
    <Sidebar />
    <main class="flex-1">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import Sidebar from '@/Components/Sidebar.vue'
</script>