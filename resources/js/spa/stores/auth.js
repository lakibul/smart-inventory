import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,

    hasRole: (state) => (role) => {
      return state.user?.roles?.includes(role) || false
    },

    hasPermission: (state) => (permission) => {
      return state.user?.permissions?.includes(permission) || false
    },

    hasAnyPermission: (state) => (permissions) => {
      if (!state.user?.permissions) return false
      return permissions.some(permission => state.user.permissions.includes(permission))
    }
  },

  actions: {
    async login(credentials) {
      try {
        const response = await axios.post('/auth/login', credentials)

        if (response.data.success) {
          this.token = response.data.data.token
          this.user = response.data.data.user

          // Store token in localStorage
          localStorage.setItem('token', this.token)

          // Set default authorization header
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

          return response.data
        } else {
          throw new Error(response.data.message || 'Login failed')
        }
      } catch (error) {
        // Clear any existing auth data
        this.logout()

        if (error.response?.data?.message) {
          throw new Error(error.response.data.message)
        } else if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          throw new Error(errors.join(', '))
        } else {
          throw new Error('Login failed. Please try again.')
        }
      }
    },

    async logout() {
      try {
        if (this.token) {
          await axios.post('/auth/logout')
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        // Clear auth data regardless of API call result
        this.user = null
        this.token = null
        localStorage.removeItem('token')
        delete axios.defaults.headers.common['Authorization']
      }
    },

    async fetchUser() {
      try {
        if (!this.token) {
          throw new Error('No token available')
        }

        // Set authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        const response = await axios.get('/auth/user')

        if (response.data.success) {
          this.user = response.data.data
          return response.data
        } else {
          throw new Error(response.data.message || 'Failed to fetch user')
        }
      } catch (error) {
        // If fetching user fails, logout
        this.logout()
        throw error
      }
    },

    // Initialize auth state on app start
    init() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      }
    }
  }
})
