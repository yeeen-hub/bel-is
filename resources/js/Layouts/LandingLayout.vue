<script setup>
import { Link } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'
</script>

<template>
  <div class="min-h-screen flex flex-col">

    <img 
      src="/images/bg1.jpg" 
      class="absolute inset-0 w-full h-full object-cover -z-10"
    />

    <header class="flex justify-between items-center p-7 text-black">
      <h1 class="font-heading text-xl text-white">BEL-IS</h1>

      <nav class="fixed top-7 left-1/2 -translate-x-1/2 z-50">
        <div class="inline-flex bg-white bg-opacity-20 backdrop-blur-sm px-6 py-3 rounded-md space-x-4 font-paragraph text-base text-black">
          <Link :href="route('home')" class="hover:underline hover:decoration-blue-700 hover:underline-offset-4 transition">
              Home
          </Link>
          <a href="#attractions" class="hover:underline hover:decoration-blue-700 hover:underline-offset-4 transition">Attractions</a>
          <a href="#map" class="hover:underline hover:decoration-blue-700 hover:underline-offset-4 transition">Map</a>
          <a href="#contact" class="hover:underline hover:decoration-blue-700 hover:underline-offset-4 transition">Contact</a>
          <a href="#about" class=" hover:underline hover:decoration-blue-700 hover:underline-offset-4 transition">About</a>
        </div>
      </nav>

      <div>
        <Link :href="route('login')"> 
          <img src="/images/brgylogo.png" alt="Barangay Logo" class="h-14 object-cover">
        </Link>
      </div>

    </header>

    <main class="flex-1 px-4">
      <slot /> <!-- This is where the page content will appear -->
    </main>

     <footer class="bg-black text-white mt-16">
      <div class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div>
          <h2 class="text-xl font-bold mb-2">BEL-IS</h2>
        
        </div>

        <div class="flex items-center justify-center">
          <ul class="flex gap-6 text-sm text-gray-300">
            <li><a href="#home" class="hover:text-white">Home</a></li>
            <li><a href="#attractions" class="hover:text-white">Attractions</a></li>
            <li><a href="#map" class="hover:text-white">Map</a></li>
            <li><a href="#contact" class="hover:text-white">Contact</a></li>
          </ul>
        </div>

        <div class="flex justify-end items-center">
          <button class="group flex items-center gap-2 border-2 border-white text-white px-6 py-3 rounded-full 
               hover: hover:border-blue-500 transition">
            Explore Destination
            <svg class="w-5 h-5 stroke-current group-hover:text-white group-hover:stroke-blue-500 transition" 
                fill="none" stroke-width="2" viewBox="0 0 24 24">
              <path d="M5 12h14M13 5l7 7-7 7"/>
            </svg>
          </button>
        </div>

      </div>

      <hr/>
      
      <div class="flex items-center justify-center text-center text-sm text-gray-400 p-4 gap-8">

        <p> Terms of Use</p>
        <p> Privacy Policy</p>
        <p>© 2026 Bel-is Developer Team. All Rights Reserved</p>
        
      </div>
    </footer>
  </div>
</template>