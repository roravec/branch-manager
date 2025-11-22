import { defineStore } from 'pinia'
import api from '@/api.js'

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: []
  }),

  getters: {
    allUsers: (state) => state.users,
  },

  actions: {
    async loadUsers() {
      const res = await api.get('/users')
      this.users = res.data
    }
  }
})
