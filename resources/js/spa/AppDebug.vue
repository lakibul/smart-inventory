<template>
  <div id="app-debug" style="padding: 20px; font-family: Arial;">
    <h1>Smart Inventory Debug</h1>
    <p v-if="mounted">✓ Vue.js is mounted and working!</p>
    <p v-else>Loading Vue.js...</p>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">
      <h3>Debug Information:</h3>
      <ul>
        <li>Auth Store Available: {{ authStoreAvailable }}</li>
        <li>Router Available: {{ routerAvailable }}</li>
        <li>Current Route: {{ currentRoute }}</li>
        <li>Is Authenticated: {{ isAuth }}</li>
      </ul>
    </div>

    <div style="margin-top: 20px;">
      <button @click="testLogin" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px;">
        Test Login Page
      </button>
      <button @click="testAPI" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; margin-left: 10px;">
        Test API
      </button>
    </div>

    <div v-if="apiResult" style="margin-top: 15px; padding: 10px; background: #e9ecef; border-radius: 4px;">
      <strong>API Test Result:</strong>
      <pre>{{ apiResult }}</pre>
    </div>

    <!-- Main app content -->
    <div style="margin-top: 30px; border-top: 1px solid #ccc; padding-top: 20px;">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import axios from 'axios'

const router = useRouter()

// Debug states
const mounted = ref(false)
const authStoreAvailable = ref(false)
const routerAvailable = ref(false)
const currentRoute = ref('')
const isAuth = ref(false)
const apiResult = ref('')

let authStore = null

onMounted(() => {
  mounted.value = true

  try {
    authStore = useAuthStore()
    authStoreAvailable.value = true
    isAuth.value = authStore.isAuthenticated
  } catch (error) {
    console.error('Auth store error:', error)
  }

  try {
    routerAvailable.value = !!router
    currentRoute.value = router.currentRoute.value.path
  } catch (error) {
    console.error('Router error:', error)
  }
})

const testLogin = () => {
  router.push('/login')
}

const testAPI = async () => {
  try {
    const response = await axios.get('/api/test')
    apiResult.value = JSON.stringify(response.data, null, 2)
  } catch (error) {
    apiResult.value = 'Error: ' + error.message
  }
}
</script>
