import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null,
    user: null,
  }),
  actions: {
    setToken(newToken) {
      this.token = newToken;
      localStorage.setItem('auth_token', newToken);
    },
    logout() {
      this.token = null;
      localStorage.removeItem('auth_token');
    }
  }
});