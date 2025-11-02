import { defineStore } from 'pinia'
import api from '@/api.js'

export const useBranchStore = defineStore('branches', {
  state: () => ({
    branches: [],
    loading: false,
    error: null,
  }),

  getters: {
    getById: (state) => (id) => state.branches.find(b => b.id == id),
    allBranches: (state) => state.branches,
    branchCount: (state) => state.branches.length,
  },

  actions: {
    async loadBranches() {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/branches')
        this.branches = response.data
      } catch (err) {
        console.error('Chyba pri načítaní pobočiek:', err)
        this.error = err.message || 'Nepodarilo sa načítať pobočky'
      } finally {
        this.loading = false
      }
    },

    async addBranch(branchData) {
      try {
        const response = await api.post('/branch', branchData)
        this.branches.push(response.data)
        return response.data
      } catch (err) {
        console.error('Chyba pri vytváraní pobočky:', err)
        throw err
      }
    },

    async updateBranch(id, updatedData) {
      try {
        const response = await api.post(`/edit/branch/${id}`, updatedData)
        const index = this.branches.findIndex(b => b.id == id)
        if (index !== -1) this.branches[index] = response.data
        return response.data
      } catch (err) {
        console.error('Chyba pri úprave pobočky:', err)
        throw err
      }
    },

    async createBranch(newData) {
      try {
        const response = await api.post(`/branch`, newData)
        this.branches.push(response.data);
        return response.data
      } catch (err) {
        console.error('Chyba pri vytváraní pobočky:', err)
        throw err
      }
    },

    async deleteBranch(id) {
      try {
        await api.delete(`/branch/${id}`)
        this.branches = this.branches.filter(b => b.id != id)
      } catch (err) {
        console.error('Chyba pri mazaní pobočky:', err)
        throw err
      }
    },
  },
})
