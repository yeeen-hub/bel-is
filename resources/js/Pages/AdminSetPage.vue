<template>
    <LandingLayout>
      <div class="container mx-auto">
            <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-4">
                <div class="relative flex-1">
                    <input type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />
                </div> 
                <FontAwesomeIcon icon="bell" />
                <FontAwesomeIcon icon="user" />
            </div>
        </div>

        <div class="p-4 mt-4 rounded-lg flex flex-col">

            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">System Setting</h1>
                    <p class="text-sm text-gray-500">Setup and edit system settings and preferences.</p>
                </div>

                 <input
                        type="text"
                        placeholder="Search..."
                        class="w-25 p-2 rounded-lg border-2 border-gray-200 focus:border-gray-300 focus:ring-0"
                    />
            </div>
        </div>

        <div class="flex space-between justify-center">
          <Link :href="route('systemsettings')"
                class="px-4 py-2 text-gray-700 rounded hover:text-gray-900 font-semibold">
            System Settings
          </Link>
          <Link :href="route('usermanagement')" 
            class="px-4 py-2 text-gray-700 rounded hover:text-gray-900 font-semibold">
            User Management
          </Link>
          
          <Link :href="route('auditlogs')" 
                class="px-4 py-2 text-gray-700 rounded hover:text-gray-900 font-semibold">
            Audit Logs
          </Link>
          
          <Link :href="route('websitecontent')" 
                class="px-4 py-2 text-gray-700 rounded hover:text-gray-900 font-semibold">
            Website Content
          </Link>
          
          <Link :href="route('virtualtour')" 
                class="px-4 py-2 text-gray-700 rounded hover:text-gray-900 font-semibold">
            Virtual Tour
          </Link>
          
          <Link :href="route('securitysettings')" 
                class="px-4 py-2 text-gray-700 rounded hover:text-gray-900 font-semibold">
            Security
          </Link>
          

        </div>

        <div class="max-w-2xl mx-auto mt-10">
          <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
            <div class=" flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-700">
                Environmental Fee Management
              </p>
              <p class="text-sm text-gray-500">
                Manage fees based on tourist category.
              </p>
            </div>
            <button
              type="button"
              class="border border-blue-500 text-blue-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-900 hover:text-white transition">
              Edit
            </button>
          </div>

          <table class="w-full text-left mt-5 border-collapse text-center bg-white rounded-lg ">
            <thead class="border-l border-r  border-t border-b bg-gray-50 rounded-lg">
                <tr>
                    <th class="p-2 text-black">Category</th>
                    <th class="p-2 text-black ">Age Range</th>
                    <th class="p-2 text-black ">Fee</th>
                </tr>
            </thead>

            <tbody>
                <tr @click="showModal = true" class="cursor-pointer border-l border-r  border-t border-b bg-gray-50 rounded-lg hover:bg-gray-100">
                    <td class="p-2 border-b">Child</td>
                    <td class="p-2 border-b">0-12 years</td>
                    <td class="p-2 border-b">$10.00</td>
                </tr>
            </tbody>
        </table>
        <p class="text-sm text-gray-500 mt-2">
          Last Updated: June 10, 2024 at 3:45 PM by Admin
        </p>
        
        <div class="flex justify-end mt-5">
          <button
            type="button"
            class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
            Save Changes
          </button>
        </div>
          </div>
          
        </div>
    </LandingLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js'; // <-- correct import
import LandingLayout from '@/Layouts/SidebarLayout.vue';
</script>