<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const activeSection = ref('home')
let observer = null

onMounted(() => {
  const sections = document.querySelectorAll('section[data-section]')

  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          activeSection.value = entry.target.dataset.section
        }
      })
    },
    { threshold: 0.4 }
  )

  sections.forEach(s => observer.observe(s))
})

onUnmounted(() => {
  if (observer) observer.disconnect()
})

function navClass(section) {
  return activeSection.value === section
    ? 'underline decoration-blue-700 underline-offset-4 font-semibold transition'
    : 'hover:underline hover:decoration-blue-700 hover:underline-offset-4 transition'
}
</script>

<template>
  <div class="min-h-screen flex flex-col">

    <!-- Fixed Header -->
    <header class="fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-7 py-5 pointer-events-none">
      <h1 class="font-heading text-xl text-white pointer-events-auto">BEL-IS</h1>

      <nav class="pointer-events-auto">
        <div class="inline-flex bg-white bg-opacity-20 backdrop-blur-sm px-6 py-3 rounded-md space-x-4 font-paragraph text-base text-black">
          <Link :href="route('home')" :class="navClass('home')">Home</Link>
          <a href="#attractions" :class="navClass('attractions')">Attractions</a>
          <a href="#map" :class="navClass('map')">Map</a>
          <a href="#about" :class="navClass('about')">About</a>
          <a href="#pre-register" :class="navClass('pre-register')">Pre-Register</a>
          <a href="#contact" :class="navClass('contact')">Contact</a>
        </div>
      </nav>

      <div class="pointer-events-auto">
        <Link :href="route('login')">
          <img src="/images/brgylogo.png" alt="Barangay Logo" class="h-14 object-cover">
        </Link>
      </div>
    </header>

    <!-- Page content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-black text-white">
      <div class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 items-center">

        <div>
          <h2 class="text-xl font-bold">BEL-IS</h2>
        </div>

        <div class="flex items-center justify-center">
          <ul class="flex flex-nowrap items-center gap-x-4 text-xs text-gray-300">
            <li><a href="#home" class="hover:text-white whitespace-nowrap">Home</a></li>
            <li><a href="#attractions" class="hover:text-white whitespace-nowrap">Attractions</a></li>
            <li><a href="#map" class="hover:text-white whitespace-nowrap">Map</a></li>
            <li><a href="#about" class="hover:text-white whitespace-nowrap">About</a></li>
            <li><a href="#pre-register" class="hover:text-white whitespace-nowrap">Pre-Register</a></li>
            <li><a href="#contact" class="hover:text-white whitespace-nowrap">Contact</a></li>
          </ul>
        </div>

        <div class="flex justify-end items-center">
          <button class="group flex items-center gap-2 border-2 border-white text-white px-6 py-3 rounded-full hover:border-blue-500 transition">
            Explore Destination
            <svg class="w-5 h-5 stroke-current group-hover:stroke-blue-500 transition"
                fill="none" stroke-width="2" viewBox="0 0 24 24">
              <path d="M5 12h14M13 5l7 7-7 7"/>
            </svg>
          </button>
        </div>

      </div>

      <hr class="border-gray-700"/>

      <div class="flex items-center justify-center text-center text-sm text-gray-400 p-4 gap-8">
        <p>Terms of Use</p>
        <p>Privacy Policy</p>
        <p>© 2026 Bel-is Developer Team. All Rights Reserved</p>
      </div>
    </footer>

  </div>
</template>