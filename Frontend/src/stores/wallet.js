import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useWalletStore = defineStore('wallet', {
  state: () => ({
    balance: 0,
    transactions: [],
    loadingBalance: false,
    loadingTransactions: false,
  }),

  actions: {
    async fetchBalance() {
      this.loadingBalance = true
      try {
        const { data } = await api.get('/wallet/balance')
        this.balance = Number(data?.balance) || 0
      } finally {
        this.loadingBalance = false
      }
    },

    async fetchTransactions() {
      this.loadingTransactions = true
      try {
        const { data } = await api.get('/wallet/transactions')
        this.transactions = data.data
      } finally {
        this.loadingTransactions = false
      }
    },

    async deposit(cardData) {
      const { data } = await api.post('/wallet/deposit', cardData)
      await this.fetchBalance()
      return data
    },

    async withdraw(bankData) {
      const { data } = await api.post('/wallet/withdraw', bankData)
      await this.fetchBalance()
      return data
    },
  },
})
