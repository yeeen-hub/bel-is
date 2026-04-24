<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center 
            bg-gradient-to-b from-white to-[#104C97] 
            px-4 sm:px-6 lg:px-0 py-10 sm:py-0">

    <!-- TITLE -->
    <div class="text-center">
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
            Admin Log-In
        </h1>
    </div>

    <!-- FORM WRAPPER -->
    <div
        class="mt-8 w-full max-w-sm sm:max-w-md 
               overflow-hidden px-5 sm:px-6 py-6 sm:py-4 
               rounded-xl sm:rounded-lg shadow-lg"
    >
        <slot />
    </div>

</div>
</template>
