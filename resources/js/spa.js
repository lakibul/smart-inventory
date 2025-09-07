import './bootstrap'
import '../css/app.css'

import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'
import { createPinia } from 'pinia'
import axios from 'axios'

console.log('Starting Vue app initialization...')

// Setup axios defaults
axios.defaults.baseURL = '/api'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept'] = 'application/json'

// Add CSRF token to requests
const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
    console.log('CSRF token added to axios headers')
}

// Simple inline components to avoid import issues
const Login = {
    template: `
        <div style="padding: 20px; font-family: Arial, sans-serif; background: #f5f5f5; min-height: 100vh;">
            <div style="max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <h1 style="text-align: center; color: #333; margin-bottom: 30px;">Smart Inventory</h1>
                <form @submit.prevent="handleLogin">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #555;">Email:</label>
                        <input
                            v-model="form.email"
                            type="email"
                            style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box;"
                            placeholder="Enter your email"
                            required
                        />
                    </div>
                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #555;">Password:</label>
                        <input
                            v-model="form.password"
                            type="password"
                            style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box;"
                            placeholder="Enter your password"
                            required
                        />
                    </div>
                    <button
                        type="submit"
                        style="width: 100%; background: #007bff; color: white; padding: 14px; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s;"
                        :disabled="loading"
                        @mouseover="$event.target.style.background = loading ? '#007bff' : '#0056b3'"
                        @mouseout="$event.target.style.background = '#007bff'"
                    >
                        {{ loading ? 'Logging in...' : 'Login' }}
                    </button>
                </form>
                <div v-if="error" style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-top: 20px; border: 1px solid #f5c6cb;">
                    {{ error }}
                </div>
                <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
                    Demo: admin@example.com / password
                </div>
            </div>
        </div>
    `,
    data() {
        return {
            form: {
                email: 'admin@example.com',
                password: 'password'
            },
            loading: false,
            error: null
        }
    },
    methods: {
        async handleLogin() {
            console.log('Login attempt started')
            this.loading = true
            this.error = null

            try {
                const response = await axios.post('/auth/login', this.form)
                console.log('Login successful:', response.data)

                localStorage.setItem('token', response.data.token)
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`

                this.$router.push('/dashboard')
            } catch (error) {
                console.error('Login error:', error)
                this.error = error.response?.data?.message || 'Login failed. Please try again.'
            } finally {
                this.loading = false
            }
        }
    },
    mounted() {
        console.log('Login component mounted')
    }
}

const Dashboard = {
    template: `
        <div style="padding: 20px; font-family: Arial, sans-serif; background: #f5f5f5; min-height: 100vh;">
            <div style="max-width: 800px; margin: 0 auto;">
                <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-bottom: 20px;">
                    <h1 style="color: #333; margin-bottom: 10px;">Smart Inventory Dashboard</h1>
                    <p style="color: #666; margin-bottom: 20px;">Welcome back! Here's your inventory overview.</p>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        <div v-for="stat in stats" :key="stat.label" style="background: #f8f9fa; padding: 20px; border-radius: 6px; text-align: center;">
                            <div style="font-size: 24px; font-weight: bold; color: #007bff;">{{ stat.value }}</div>
                            <div style="color: #666; margin-top: 5px;">{{ stat.label }}</div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button
                            @click="$router.push('/products')"
                            style="background: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;"
                        >
                            Manage Products
                        </button>
                        <button
                            @click="$router.push('/categories')"
                            style="background: #17a2b8; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;"
                        >
                            Categories
                        </button>
                        <button
                            @click="$router.push('/warehouses')"
                            style="background: #ffc107; color: #212529; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;"
                        >
                            Warehouses
                        </button>
                        <button
                            @click="logout"
                            style="background: #dc3545; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `,
    data() {
        return {
            stats: [
                { label: 'Total Products', value: '0' },
                { label: 'Categories', value: '0' },
                { label: 'Warehouses', value: '0' },
                { label: 'Low Stock Items', value: '0' }
            ]
        }
    },
    methods: {
        logout() {
            console.log('Logout clicked')
            localStorage.removeItem('token')
            delete axios.defaults.headers.common['Authorization']
            this.$router.push('/login')
        },
        async loadStats() {
            try {
                const response = await axios.get('/dashboard/stats')
                // Update stats when API returns data
                console.log('Dashboard stats:', response.data)
            } catch (error) {
                console.error('Error loading stats:', error)
            }
        }
    },
    mounted() {
        console.log('Dashboard component mounted')
        this.loadStats()
    }
}

const App = {
    template: '<div><router-view></router-view></div>',
    mounted() {
        console.log('App component mounted')
    }
}

// Setup router
const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        {
            path: '/',
            redirect: '/login'
        },
        {
            path: '/login',
            name: 'login',
            component: Login
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: Dashboard
        }
    ]
})

console.log('Router configured')

// Create Pinia store
const pinia = createPinia()

// Create Vue app
const app = createApp(App)

// Use plugins
app.use(router)
app.use(pinia)

// Make axios available globally
app.config.globalProperties.$http = axios

// Add router navigation guards
router.beforeEach((to, from, next) => {
    const authToken = localStorage.getItem('token')

    if (authToken) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
    }

    console.log(`Navigating from ${from.path} to ${to.path}`)
    next()
})

// Mount app
try {
    app.mount('#app')
    console.log('Vue app mounted successfully!')
} catch (error) {
    console.error('Error mounting Vue app:', error)
}
