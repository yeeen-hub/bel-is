import { createRouter, createWebHistory } from 'vue-router'

import HomeLayout from '@/Layouts/VirtualTourLayout/VTHomeLayout.vue' 
import BelisLayout from '@/Layouts/VirtualTourLayout/BelisLayout.vue'

const routes = [
  {
    path: '/VTHome',
    name: 'home',
    component: HomeLayout
  },
  {
    path: '/location/:id',
    component: BelisLayout,
    children: [
      {
        path: '',
        name: 'location-view',
        component: () => import('@/Pages/VirtualTour/LocationView.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router