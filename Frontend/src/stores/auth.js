import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null,
    user: JSON.parse(localStorage.getItem('auth_user')) || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    userRole: (state) => state.user?.role || null,
    isFreelancer: (state) => state.user?.role === 'freelancer',
    isAbogado: (state) => state.user?.role === 'abogado',
  },

  actions: {
    setAuth(token, user) {
      this.token = token
      this.user = user
      localStorage.setItem('auth_token', token)
      localStorage.setItem('auth_user', JSON.stringify(user))
    },

    setUser(user) {
      this.user = user
      localStorage.setItem('auth_user', JSON.stringify(user))
    },

    async fetchUser() {
      try {
        const { data } = await api.get('/auth/me')
        this.setUser(data.data)
      } catch {
        this.token = null
        this.user = null
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } catch {
        // token may already be invalid
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    },
  },
})
