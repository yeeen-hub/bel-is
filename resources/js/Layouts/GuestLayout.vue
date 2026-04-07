<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="bg-gradient-to-b from-white to-[#104C97] flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <h1 class="text-6xl font-bold text-gray-900 "> Admin Log-In </h1>
        </div>

        <div
            class="mt-6 w-full overflow-hidden px-6 py-4 sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
