<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <!-- Normal scrolling wrapper — no snap, no fixed height -->
  <div class="min-h-screen flex flex-col bg-gray-50">

    <!-- Fixed Header — solid white for light-background pages -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-100 shadow-sm flex justify-between items-center px-7 py-4">
      <Link :href="route('home')" class="font-heading text-xl font-bold text-gray-900">BEL-IS</Link>

      <nav>
        <div class="inline-flex gap-4 font-paragraph text-sm text-gray-600">
          <Link :href="route('home')" class="hover:text-gray-900 transition">Home</Link>
          <a :href="route('home') + '#attractions'" class="hover:text-gray-900 transition">Attractions</a>
          <a :href="route('home') + '#map'" class="hover:text-gray-900 transition">Map</a>
          <a :href="route('home') + '#about'" class="hover:text-gray-900 transition">About</a>
          <a href="/pre-register" class="text-gray-900 font-semibold underline underline-offset-4 decoration-blue-600">Pre-Register</a>
          <a :href="route('home') + '#contact'" class="hover:text-gray-900 transition">Contact</a>
        </div>
      </nav>

      <Link :href="route('login')">
        <img src="/images/brgylogo.png" alt="Barangay Logo" class="h-10 object-cover rounded-full">
      </Link>
    </header>

    <!-- Page content — pt-20 clears the fixed header -->
    <main class="flex-1 pt-20">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-auto">
      <div class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">

        <div>
          <h2 class="text-xl font-bold mb-2">BEL-IS</h2>
          <p class="text-sm text-gray-400">Barangay Bel-is, Buruanga, Aklan</p>
        </div>

        <div class="flex items-center justify-center">
          <ul class="flex flex-wrap gap-6 text-sm text-gray-300 justify-center">
            <li><Link :href="route('home')" class="hover:text-white">Home</Link></li>
            <li><a :href="route('home') + '#attractions'" class="hover:text-white">Attractions</a></li>
            <li><a :href="route('home') + '#map'" class="hover:text-white">Map</a></li>
            <li><a :href="route('home') + '#about'" class="hover:text-white">About</a></li>
            <li><a href="/pre-register" class="hover:text-white">Pre-Register</a></li>
            <li><a :href="route('home') + '#contact'" class="hover:text-white">Contact</a></li>
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