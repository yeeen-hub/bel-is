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

        <div class="mt-5">
            <h1 class="text-lg font-semibold text-gray-800">Audit Logs</h1>

            <div class="flex items-center justify-between w-full mt-2">

                    <div class="flex items-center gap-3">
                        <button 
                            class="w-full flex items-center justify-between border border-gray-300 p-1 px-2 rounded-lg"
                        >
                            <div class="flex items-center space-x-2">
                                <span class="font-semibold">User</span>
                            </div>

                            <svg class="w-4 h-4 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>
                        </button>

                        <button 
                            class="w-full flex items-center justify-between border border-gray-300 p-1 px-2 rounded-lg"
                        >
                            <div class="flex items-center space-x-2">
                                <span class="font-semibold">Actions</span>
                            </div>

                            <svg class="w-4 h-4 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>
                        </button>
                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="flex items-center gap-2">
                        <input type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />

                        <button class="h-10 border bg-blue-500 text-white font-bold px-3 text-sm rounded-lg">
                        Export Logs
                        </button>
                    </div>

                </div>
            </div>

            <div class="p-6 bg-gray-50 min-h-screen">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-gray-50/50 text-xs font-semibold uppercase tracking-wider text-gray-600">
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Date & Time</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Description</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="/images/avatar.jpg" class="h-10 w-10 rounded-full object-cover border border-gray-100" />
                            <span class="font-medium text-gray-900">John Doe</span>
                        </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">Admin</td>
                        <td class="px-6 py-4 text-gray-600">March 20, 2023 10:30 AM</td>
                        <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-sm font-medium text-emerald-600">Updates</span>
                        </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">Making Updates</td>
                    </tr>
                    </tbody>
                </table>
                </div>
            </div>

        
    </LandingLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js'; // <-- correct import
import LandingLayout from '@/Layouts/SidebarLayout.vue';
</script>