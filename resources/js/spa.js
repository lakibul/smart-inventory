import './bootstrap'
import '../css/app.css'

import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'
import { createPinia } from 'pinia'
import axios from 'axios'

// Simple components for testing
const Login = {
    template: `
        <div style="padding: 20px; font-family: Arial;">
            <h1>Smart Inventory - Login</h1>
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 400px;">
                <form @submit.prevent="handleLogin">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Email:</label>
                        <input
                            v-model="form.email"
                            type="email"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                            placeholder="admin@example.com"
                        />
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Password:</label>
                        <input
                            v-model="form.password"
                            type="password"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                            placeholder="password"
                        />
                    </div>
                    <button
                        type="submit"
                        style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;"
                        :disabled="loading"
                    >
                        {{ loading ? 'Logging in...' : 'Login' }}
                    </button>
                </form>
                <div v-if="error" style="color: red; margin-top: 10px;">
                    {{ error }}
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
            this.loading = true
            this.error = null

            try {
                const response = await axios.post('/auth/login', this.form)
                localStorage.setItem('token', response.data.token)
                this.$router.push('/dashboard')
            } catch (error) {
                this.error = error.response?.data?.message || 'Login failed'
            } finally {
                this.loading = false
            }
        }
    }
}

const Dashboard = {
    template: `
        <div style="padding: 20px; font-family: Arial;">
            <h1>Smart Inventory - Dashboard</h1>
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <p>Welcome to the Smart Inventory System!</p>
                <button
                    @click="logout"
                    style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;"
                >
                    Logout
                </button>
            </div>
        </div>
    `,
    methods: {
        logout() {
            localStorage.removeItem('token')
            this.$router.push('/login')
        }
    }
}

const App = {
    template: '<router-view></router-view>'
}

// Setup axios defaults
axios.defaults.baseURL = '/api'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept'] = 'application/json'

// Add CSRF token to requests
const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
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

// Create Pinia store
const pinia = createPinia()

// Create Vue app
const app = createApp(App)

// Use plugins
app.use(router)
app.use(pinia)

// Make axios available globally
app.config.globalProperties.$http = axios

// Mount app
app.mount('#app')

console.log('Vue app mounted successfully!')
