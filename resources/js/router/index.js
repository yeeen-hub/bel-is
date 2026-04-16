import { createRouter, createWebHistory } from 'vue-router'

import Home from '../Pages/Virtual Tour/Home.vue'
import HomeLayout from '@/Layouts/Virtual Tour/HomeLayout.vue'
import BelisLayout from '@/Layouts/Virtual Tour/BelisLayout.vue'

const routes = [
  {
    path: '/Home',
    component: HomeLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: Home
      }
    ]
  },

  {
    path: '/location/:id',
    component: BelisLayout,
    children: [
      {
        path: '',
        name: 'location-view',
        component: () => import('@/Pages/Virtual Tour/LocationView.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router