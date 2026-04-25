<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const page = usePage()

const activeSection = ref('home')
let observer = null
const mobileMenu = ref(false)

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

function goTo(sectionId) {
  const el = document.getElementById(sectionId)

  if (el) {
    el.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    })
  }

  mobileMenu.value = false
}
</script>

<template>
  <div class="min-h-screen flex flex-col">

    <!-- Fixed Header -->
    <header class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-4 sm:px-7 py-4 sm:py-5">

  <!-- Logo -->
  <h1 class="font-heading text-lg sm:text-xl text-white pointer-events-auto">
    BEL-IS
  </h1>

  <!-- Desktop Navigation -->
  <nav class="hidden lg:block pointer-events-auto">
    <div class="inline-flex bg-white/20 backdrop-blur-sm px-6 py-3 rounded-md space-x-4 font-paragraph text-sm lg:text-base text-black">
      <a @click.prevent="goTo('home')" href="#" :class="navClass('home')">Home</a>
      <a @click.prevent="goTo('attractions')" href="#" :class="navClass('attractions')">Attractions</a>
      <a @click.prevent="goTo('map')" href="#" :class="navClass('map')">Map</a>
      <a @click.prevent="goTo('about')" href="#" :class="navClass('about')">About</a>
      <a @click.prevent="goTo('pre-register')" href="#" :class="navClass('pre-register')">Pre-Register</a>
      <a @click.prevent="goTo('contact')" href="#" :class="navClass('contact')">Contact</a>
    </div>
  </nav>

  <!-- Right side -->
  <div class="flex items-center gap-3 pointer-events-auto">

    <!-- Login / Logo -->
    <Link :href="route('login')">
      <img src="/images/brgylogo.png" alt="Barangay Logo"
        class="h-10 sm:h-14 object-cover" />
    </Link>

    <!-- Mobile Hamburger -->
    <button @click="mobileMenu = !mobileMenu"
      class="lg:hidden text-black text-2xl">
      <i class="fa fa-bars"></i>
    </button>
  </div>

  <!-- Mobile Menu -->
<div v-if="mobileMenu"
  class="absolute top-full right-4 mt-3 w-52 
         bg-black/40 backdrop-blur-lg 
         border border-white/20 
         rounded-xl shadow-xl lg:hidden">

  <div class="flex flex-col p-4 space-y-3 text-sm font-medium text-white">

    <a @click.prevent="goTo('home')" class="text-white">
  Home
</a>

    <a @click.prevent="goTo('attractions')" class="hover:text-blue-400 transition">Attractions</a>

    <a @click.prevent="goTo('map')" class="hover:text-blue-400 transition">Map</a>

    <a @click.prevent="goTo('about')" class="hover:text-blue-400 transition">About</a>

    <a @click.prevent="goTo('pre-register')" class="hover:text-blue-400 transition">Pre-Register</a>

    <a @click.prevent="goTo('contact')" class="hover:text-blue-400 transition">Contact</a>

  </div>
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

  <div class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 items-center text-center md:text-left">

    <!-- Logo -->
    <div>
      <h2 class="text-lg sm:text-xl font-bold">BEL-IS</h2>
    </div>

    <!-- Navigation -->
    <div class="flex justify-center">
      <ul class="flex flex-wrap justify-center gap-x-2 gap-y-2 text-xs sm:text-sm text-gray-200">
        <li><a @click.prevent="scrollTo('home')" href="#" class="hover:text-white cursor-pointer">Home</a></li>
        <li><a @click.prevent="scrollTo('attractions')" href="#" class="hover:text-white cursor-pointer">Attractions</a></li>
        <li><a @click.prevent="scrollTo('map')" href="#" class="hover:text-white cursor-pointer">Map</a></li>
        <li><a @click.prevent="scrollTo('about')" href="#" class="hover:text-white cursor-pointer">About</a></li>
        <li><a @click.prevent="scrollTo('pre-register')" href="#" class="hover:text-white cursor-pointer">Pre-Register</a></li>
        <li><a @click.prevent="scrollTo('contact')" href="#" class="hover:text-white cursor-pointer">Contact</a></li>
      </ul>
    </div>

    <!-- Button -->
    <div class="flex justify-center md:justify-end">
      <button
        class="group flex items-center gap-2 border border-white text-white px-4 sm:px-6 py-2 sm:py-3 rounded-full hover:border-blue-500 transition text-sm sm:text-base">

        Explore Destination

        <svg class="w-4 h-4 sm:w-5 sm:h-5 stroke-current group-hover:stroke-blue-500 transition"
          fill="none" stroke-width="2" viewBox="0 0 24 24">
          <path d="M5 12h14M13 5l7 7-7 7"/>
        </svg>

      </button>
    </div>

  </div>

  <hr class="border-gray-700"/>

  <div class="flex flex-col sm:flex-row items-center justify-center text-center text-xs sm:text-sm text-gray-400 p-4 gap-2 sm:gap-8">

    <p>Terms of Use</p>
    <p>Privacy Policy</p>
    <p>© 2026 Bel-is Developer Team. All Rights Reserved</p>

  </div>

</footer>

  </div>
</template>