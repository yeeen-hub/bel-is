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

        <div class="border-b border-gray-300 flex justify-center gap-6">

            <Link
            :href="route('settings')"
            :class="navClass('settings')"
            >
            System Settings
            </Link>

            <Link
            :href="route('usermanagement')"
            :class="navClass('usermanagement')"
            >
            User Management
            </Link>

            <Link
            :href="route('auditlogs')"
            :class="navClass('auditlogs')"
            >
            Audit Logs
            </Link>

            <Link
            :href="route('websitecontent')"
            :class="navClass('websitecontent')"
            >
            Website Content
            </Link>

            <Link
            :href="route('virtualtour')"
            :class="navClass('virtualtour')"
            >
            Virtual Tour
            </Link>

            <Link
            :href="route('securitysettings')"
            :class="navClass('securitysettings')"
            >
            Security
            </Link>

        </div>

        <div class="flex items-center justify-between w-full mt-5">

                <div class="flex items-center gap-3">
                    <h1 class="text-lg font-semibold text-gray-800">All Users</h1>
                </div>

                <!-- RIGHT SIDE -->
                <div class="flex items-center gap-2">
                    <input type="text" placeholder="Search..." class="w-25 p-2 rounded-lg border-transparent focus:border-gray-300 focus:ring-0" />

                    <div class="flex items-center gap-2">
                        <button
                            ref="filterBtnRef"
                            @click="toggleFilter"
                            class="relative flex items-center gap-1.5 text-sm border px-3 py-2 rounded transition"
                            :class="activeFilterCount > 0
                                ? 'border-gray-800 bg-gray-900 text-white'
                                : 'border-gray-300 text-gray-700 hover:bg-gray-200'">
                            <FontAwesomeIcon icon="filter" class="text-xs" />
                            Filters
                            <span v-if="activeFilterCount > 0"
                                class="bg-white text-gray-900 text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center">
                                {{ activeFilterCount }}
                            </span>
                        </button>
                    </div>

                    <button class="h-10 border bg-blue-500 text-white font-bold px-3 text-sm rounded-lg">
                    Add User
                    </button>
                </div>

            </div>

            <div class="p-6 bg-gray-50 min-h-screen">
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50/50 text-xs font-semibold uppercase tracking-wider text-gray-600">
            <th class="px-6 py-4 w-10">
              <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            </th>
            <th class="px-6 py-4">Name</th>
            <th class="px-6 py-4">E-mail</th>
            <th class="px-6 py-4">Contact No.</th>
            <th class="px-6 py-4">Role</th>
            <th class="px-6 py-4">Status</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">
              <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <img src="/images/avatar.jpg" class="h-10 w-10 rounded-full object-cover border border-gray-100" />
                <span class="font-medium text-gray-900">John Doe</span>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-600">john.doe@example.com</td>
            <td class="px-6 py-4 text-gray-600">123-456-7890</td>
            <td class="px-6 py-4 text-gray-600">Admin</td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-sm font-medium text-emerald-600">Active</span>
              </div>
            </td>
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

const navClass = (routeName) => [
  'pb-2 text-sm font-semibold transition border-b-2',
  route().current(routeName)
    ? 'text-gray-900 border-gray-900'
    : 'text-gray-400 border-transparent hover:text-gray-600'
]
</script>