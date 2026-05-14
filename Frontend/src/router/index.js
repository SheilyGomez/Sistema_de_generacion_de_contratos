import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'

const router = createRouter({

  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [

    {
      path: '/',
      redirect: '/login'
    },

    {
      path: '/login',
      component: LoginView
    },

    {
      path: '/register',
      name: 'register',
      component: RegisterView
    },

    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/dashboard/DashboardView.vue') // dashboard protegido, solo accesible para usuarios autenticados
    }

  ],

})

export default router