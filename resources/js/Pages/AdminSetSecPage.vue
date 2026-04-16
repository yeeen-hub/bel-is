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
            General Settings
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
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">

        <!-- COLUMN 1 -->
        <div class="mt-10">
          <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
            <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-2 p-4">
                            <div>
                                <h2 class="block text-black text-lg font-bold">Password</h2>
                                <p class="text-sm text-gray-500 mb-2">Set a password to protect your account.</p>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">Current Password</label>
                                <input class="border border-gray-400 rounded w-full py-2 px-3" type="password">
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold">New Password</label>
                                <input class="border border-gray-400 rounded w-full py-2 px-3" type="password">
                            </div>
                            <button
                    type="button"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Save Changes
                </button>
                    </div>
          </div>
        </div>

        <!-- COLUMN 2 -->
        <div class="mt-10">
          <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-9">
            <header class="mb-5">
              <h2 class="text-lg font-bold">Session Management</h2>
              <p class="text-gray-400 font-normal">Manage active sessions and secure your account</p>
            </header>

          <section class="mb-10">
            <h3 class="text-md font-bold mb-3">Current Device</h3>
            <div class="border-t border-gray-100 pt-6 flex items-start gap-4">
              <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/Google_Chrome_icon_%28February_2022%29.svg" alt="Chrome" class="w-8 h-8" />
              <div class="flex-1">
                <h4 class="font-bold text-base leading-tight">Chrome — Windows</h4>
                <div class="text-sm text-gray-400 mt-1">
                  Taguig, PH <span class="text-green-500 font-medium ml-1">[Active Now]</span>
                  <span class="mx-2 text-gray-300">|</span>
                  Last login: Apr 4, 10:32 PM
                </div>
              </div>
            </div>
          </section>

          <section class="mb-12">
            <h3 class="text-lg font-bold mb-3">Other Sessions</h3>
            <div class="border-t border-gray-100 pt-4 space-y-6">
              <div class="flex items-start gap-4">
                <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/Google_Chrome_icon_%28February_2022%29.svg" alt="Chrome" class="w-8 h-8" />
                <div class="flex-1">
                  <h4 class="font-bold text-base leading-tight">Android</h4>
                  <div class="text-sm text-gray-400 mt-1">
                    Manila, PH <span class="text-red-500 font-medium ml-1">[2hrs ago]</span>
                    <span class="mx-2 text-gray-300">|</span>
                    Last login: Apr 4, 10:32 PM
                  </div>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/Google_Chrome_icon_%28February_2022%29.svg" alt="Chrome" class="w-8 h-8" />
                <div class="flex-1">
                  <h4 class="font-bold text-base leading-tight">iPhone</h4>
                  <div class="text-sm text-gray-400 mt-1">
                    Cebu, PH <span class="text-red-500 font-medium ml-1">[Yesterday]</span>
                    <span class="mx-2 text-gray-300">|</span>
                    Last login: Apr 4, 10:32 PM
                  </div>
                </div>
              </div>
            </div>
          </section>

          <div class="flex justify-end gap-4">
            <button  class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
              Logout All
            </button>
            <button class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
              Logout Other Sessions
            </button>
            </div>
          </div>
        </div>

      </div>

      <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-9">
        <header class="mb-6">
          <h2 class="text-md font-bold">Role-Based Access Control</h2>
          <p class="text-gray-400">Manage permissions for each module and user role</p>
        </header>

        <div class="flex gap-3 mb-8">
          <button 
            v-for="tab in ['All Access', 'Tourism Website', 'Virtual Tour', 'Management System']" 
            :key="tab"
            :class="[
              'px-5 py-2 rounded-lg border text-sm font-medium transition-colors',
              tab === 'All Access' ? 'bg-indigo-100 border-indigo-900 text-indigo-900' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50'
            ]"
          >
            {{ tab }}
          </button>
        </div>

        <div class="overflow-hidden">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-gray-50/50">
                <th class="py-4 px-4 font-bold text-gray-700 rounded-l-xl">Role</th>
                <th v-for="action in ['View', 'Edit', 'Add', 'Delete', 'Manage']" :key="action" class="py-4 px-4 font-bold text-gray-700">
                  {{ action }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="role in roles" :key="role.name" class="border-b border-gray-100 last:border-0">
                <td class="py-5 px-4 flex items-center gap-4">
                  <img v-if="role.avatar" :src="role.avatar" class="w-10 h-10 rounded-full object-cover" />
                  <div v-else :class="['w-10 h-10 rounded-full', role.colorClass]"></div>
                  <span class="font-semibold text-gray-800">{{ role.name }}</span>
                </td>
                
                <td v-for="perm in ['view', 'edit', 'add', 'delete', 'manage']" :key="perm" class="py-5 px-4">
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="role.permissions[perm]" class="sr-only peer">
                    <div :class="[
                      'w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer transition-all duration-300',
                      'after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all',
                      'peer-checked:after:translate-x-full peer-checked:after:border-white',
                      role.activeColor
                    ]"></div>
                  </label>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-end gap-4 mt-10">
          <button class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
            Save Changes
          </button>
          <button class="text-gray-500 border border-gray-500 text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
            Reset
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-4">

        <!-- COLUMN 1 -->
        <div class="mt-10">
          <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
            <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-2 p-4">
              <div>
                <h2 class="block text-black text-lg font-bold">Login Security</h2>
                                <p class="text-sm text-gray-500 mb-2">Protect accounts from unauthorized access.</p>
                            </div>

                            <div class=" flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-black">
                Two-Factor Authentication
              </p>
              <p class="text-sm text-gray-500">
                Add an extra layer of security to your account.
              </p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div :class="[
                      'w-11 h-6 bg-blue-500 peer-focus:outline-none rounded-full peer transition-all duration-300',
                      'after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all',
                      'peer-checked:after:translate-x-full peer-checked:after:border-white'
                    ]"></div>
                  </label>
          </div>

          <div class=" flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-black">
                Limit Login Attempts
              </p>
              <p class="text-sm text-gray-500">
                Set the max attempts to login.
              </p>
            </div>
            <button 
                    class=" flex items-center justify-between border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors gap-2"
                >
                    <div class="flex items-center space-x-2">
                        <span class="font-semibold">3</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform-y"
                        :class="{ 'rotate-180': shownoattempts }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
          </div>

          <div class=" flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-black">
                Account Lockout Duration
              </p>
              <p class="text-sm text-gray-500">
                Set the duration of account to lockout.
              </p>
            </div>
            <button 
                    class=" flex items-center justify-between border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors gap-2"
                >
                    <div class="flex items-center space-x-2">
                        <span class="font-semibold">3</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform-y"
                        :class="{ 'rotate-180': shownoattempts }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
          </div>

          <div class=" flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-black">
                Require Strong Passwords
              </p>
              <p class="text-sm text-gray-500">
                Minimum 8 characters, uppercase and numbers.
              </p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div :class="[
                      'w-11 h-6 bg-blue-500 peer-focus:outline-none rounded-full peer transition-all duration-300',
                      'after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all',
                      'peer-checked:after:translate-x-full peer-checked:after:border-white'
                    ]"></div>
                  </label>
          </div>
                            <button
                    type="button"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    Save Changes
                </button>
                    </div>
          </div>
        </div>

        <!-- COLUMN 2 -->
        <div class="mt-10">
          <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-9">
            <div>
                <h2 class="block text-black text-lg font-bold">Activity Monitoring</h2>
                                <p class="text-sm text-gray-500 mb-2">Track system activity and user actions.</p>
                            </div>

          <section class="mb-10">
            <p class="text-md font-bold mb-3">Recent Activities</p>
            <div class="border-t border-gray-300 pt-6 flex items-start gap-4">
            </div>
            <div class=" flex items-center justify-between">
           <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-black"></span>
                <span class="text-md font-medium text-black">Admin Logged In</span>
              </div>
              <p class="text-sm text-gray-500 mb-2">2 mins ago</p>
          </div>
          </section>
          <div class="flex justify-end">
            <button
                    type="button"
                    class="bg-gray-900 text-white text-sm font-bold px-5 py-2 rounded-xl disabled:opacity-50 hover:bg-gray-800 hover:text-white transition">
                    View All Activity
                </button>
          </div>
         
          </div>
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

import { ref } from 'vue';

const roles = ref([
  {
    name: 'System Admin',
    avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=80&q=80',
    activeColor: 'peer-checked:bg-blue-600',
    permissions: { view: true, edit: true, add: true, delete: true, manage: true }
  },
  {
    name: 'LGU Personnel',
    colorClass: 'bg-green-500',
    activeColor: 'peer-checked:bg-green-500',
    permissions: { view: true, edit: false, add: false, delete: false, manage: false }
  },
  {
    name: 'Barangay Staff',
    colorClass: 'bg-yellow-500',
    activeColor: 'peer-checked:bg-yellow-500',
    permissions: { view: true, edit: true, add: false, delete: false, manage: false }
  },
  {
    name: 'Tourist',
    colorClass: 'bg-gray-600',
    activeColor: 'peer-checked:bg-gray-800',
    permissions: { view: true, edit: false, add: false, delete: false, manage: false }
  }
]);
</script>