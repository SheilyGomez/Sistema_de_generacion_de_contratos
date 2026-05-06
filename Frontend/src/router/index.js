import { createRouter, createWebHistory } from 'vue-router'
import RegistroView from '@/views/RegistroView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/register',
      name: 'register',
      component: RegistroView
    },
    {
      path: '/login',
      name: 'login',
      component: RegistroView //Cambiar aqui, muestra es misma vista de registro
    },

  ],
})

export default router
