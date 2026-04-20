<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const page = usePage()

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

function scrollTo(sectionId) {
  const el = document.getElementById(sectionId)
  if (el) el.scrollIntoView({ behavior: 'smooth' })
}
</script>

<template>
  <div class="min-h-screen flex flex-col">

    <!-- Fixed Header -->
    <header class="fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-7 py-5 pointer-events-none">
      <h1 class="font-heading text-xl text-white pointer-events-auto">BEL-IS</h1>

      <nav class="pointer-events-auto">
        <div class="inline-flex bg-white bg-opacity-20 backdrop-blur-sm px-6 py-3 rounded-md space-x-4 font-paragraph text-base text-black">
          <a @click.prevent="scrollTo('home')"         href="#" :class="navClass('home')">Home</a>
          <a @click.prevent="scrollTo('attractions')"  href="#" :class="navClass('attractions')">Attractions</a>
          <a @click.prevent="scrollTo('map')"          href="#" :class="navClass('map')">Map</a>
          <a @click.prevent="scrollTo('about')"        href="#" :class="navClass('about')">About</a>
          <a @click.prevent="scrollTo('pre-register')" href="#" :class="navClass('pre-register')">Pre-Register</a>
          <a @click.prevent="scrollTo('contact')"      href="#" :class="navClass('contact')">Contact</a>
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

    <!-- ── Contact Info Band — always sits just above the footer ── -->
    <div class="w-full relative bg-cover bg-center bg-no-repeat"
      style="background-image: url('/images/abstractbg.jpg')">
      <div class="absolute inset-0 bg-black/10 backdrop-blur-sm"></div>
      <div class="relative z-10 w-full px-8 md:px-16 py-8">
        <p class="text-xs mb-3 text-gray-600">Contact Info</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <h2 class="text-xl md:text-2xl font-bold leading-tight">
              We are always <br /> happy to assist you
            </h2>
          </div>
          <div class="space-y-2">
            <p class="text-xs uppercase tracking-wide font-medium">Email Address</p>
            <div class="w-8 h-[2px] bg-black"></div>
            <p class="text-sm font-medium">{{ page.props.contact?.email || 'help@info.com' }}</p>
            <p class="text-xs text-gray-600">Assistance hours: {{ page.props.contact?.email_hours || 'Monday – Friday 6 am to 8 pm' }}</p>
          </div>
          <div class="space-y-2">
            <p class="text-xs uppercase tracking-wide font-medium">Phone Number</p>
            <div class="w-8 h-[2px] bg-black"></div>
            <p class="text-sm font-medium">{{ page.props.contact?.phone || '+63 123 456 7890' }}</p>
            <p class="text-xs text-gray-600">Assistance hours: {{ page.props.contact?.phone_hours || 'Monday – Friday 6 am to 8 pm' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-black text-white">
      <div class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 items-center">

        <div>
          <h2 class="text-xl font-bold">BEL-IS</h2>
        </div>

        <div class="flex items-center justify-center">
          <ul class="flex flex-nowrap items-center gap-x-4 text-xs text-gray-300">
            <li><a @click.prevent="scrollTo('home')"         href="#" class="hover:text-white whitespace-nowrap cursor-pointer">Home</a></li>
            <li><a @click.prevent="scrollTo('attractions')"  href="#" class="hover:text-white whitespace-nowrap cursor-pointer">Attractions</a></li>
            <li><a @click.prevent="scrollTo('map')"          href="#" class="hover:text-white whitespace-nowrap cursor-pointer">Map</a></li>
            <li><a @click.prevent="scrollTo('about')"        href="#" class="hover:text-white whitespace-nowrap cursor-pointer">About</a></li>
            <li><a @click.prevent="scrollTo('pre-register')" href="#" class="hover:text-white whitespace-nowrap cursor-pointer">Pre-Register</a></li>
            <li><a @click.prevent="scrollTo('contact')"      href="#" class="hover:text-white whitespace-nowrap cursor-pointer">Contact</a></li>
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