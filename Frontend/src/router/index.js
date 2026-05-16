import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import PlaceholderView from '@/views/PlaceholderView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    {
      path: '/',
      redirect: () => {
        const token = localStorage.getItem('auth_token')
        const user = JSON.parse(localStorage.getItem('auth_user') || 'null')
        if (token && user) {
          return user.role === 'freelancer' ? '/freelancer/home' : '/abogado/home'
        }
        return '/login'
      },
    },

    {
      path: '/login',
      component: LoginView,
    },

    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },

    // --- Freelancer ---
    {
      path: '/freelancer',
      component: AuthLayout,
      meta: { requiresAuth: true, role: 'freelancer' },
      children: [
        { path: '', redirect: '/freelancer/home' },
        { path: 'home', component: () => import('@/views/freelancer/HomeFreelancer.vue') },
        { path: 'verify', component: () => import('@/views/freelancer/VerifyContract.vue') },
        { path: 'generate', component: () => import('@/views/freelancer/CreateContract.vue') },
        { path: 'verifications', component: () => import('@/views/freelancer/VerificationsList.vue') },
        { path: 'verifications/:id', component: () => import('@/views/freelancer/VerificationDetail.vue') },
        { path: 'generations', component: () => import('@/views/freelancer/GenerationsList.vue') },
        { path: 'generations/:id', component: () => import('@/views/freelancer/GenerationDetail.vue') },
        { path: 'lawyer-reviews', component: PlaceholderView },
        { path: 'lawyer-reviews/:id', component: PlaceholderView },
        { path: 'settings', component: PlaceholderView },
      ],
    },

    // --- Abogado ---
    {
      path: '/abogado',
      component: AuthLayout,
      meta: { requiresAuth: true, role: 'abogado' },
      children: [
        { path: '', redirect: '/abogado/home' },
        { path: 'home', component: () => import('@/views/abogado/HomeAbogado.vue') },
        { path: 'requests/:id', component: PlaceholderView },
        { path: 'settings', component: PlaceholderView },
      ],
    },

    // 404 catch-all
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('auth_token')
  const user = JSON.parse(localStorage.getItem('auth_user') || 'null')
  const requiresAuth = to.matched.some((r) => r.meta.requiresAuth)
  const allowedRole = to.matched.find((r) => r.meta.role)?.meta?.role

  if (requiresAuth && !token) {
    return '/login'
  }

  if (token && user && (to.path === '/login' || to.path === '/register')) {
    return user.role === 'freelancer' ? '/freelancer/home' : '/abogado/home'
  }

  if (token && user && allowedRole && user.role !== allowedRole) {
    return user.role === 'freelancer' ? '/freelancer/home' : '/abogado/home'
  }
})

export default router
