<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="4">
        <v-card class="elevation-12">
          <v-toolbar color="primary" dark flat>
            <v-toolbar-title>Smart Inventory Login</v-toolbar-title>
          </v-toolbar>

          <v-card-text>
            <v-form @submit.prevent="handleLogin">
              <v-text-field
                v-model="form.email"
                :error-messages="errors.email"
                label="Email"
                name="email"
                prepend-icon="mdi-account"
                type="email"
                required
                :disabled="loading"
              ></v-text-field>

              <v-text-field
                v-model="form.password"
                :error-messages="errors.password"
                label="Password"
                name="password"
                prepend-icon="mdi-lock"
                :type="showPassword ? 'text' : 'password'"
                :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append="showPassword = !showPassword"
                required
                :disabled="loading"
              ></v-text-field>

              <v-alert
                v-if="errorMessage"
                type="error"
                class="mb-4"
                dismissible
                @click:close="errorMessage = ''"
              >
                {{ errorMessage }}
              </v-alert>
            </v-form>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              :loading="loading"
              @click="handleLogin"
              size="large"
              block
            >
              Login
            </v-btn>
          </v-card-actions>

          <v-divider></v-divider>

          <v-card-text class="text-center">
            <v-chip color="info" size="small" class="ma-1">
              <strong>Demo Accounts:</strong>
            </v-chip>
            <br>
            <v-chip color="success" size="small" class="ma-1">
              Super Admin: admin@smartinventory.com
            </v-chip>
            <br>
            <v-chip color="warning" size="small" class="ma-1">
              Manager: manager@smartinventory.com
            </v-chip>
            <br>
            <v-chip color="primary" size="small" class="ma-1">
              Staff: staff@smartinventory.com
            </v-chip>
            <br>
            <v-chip color="grey" size="small" class="ma-1">
              Password for all: <strong>password</strong>
            </v-chip>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Form state
const form = reactive({
  email: '',
  password: ''
})

const errors = reactive({
  email: [],
  password: []
})

const loading = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')

// Handle login
const handleLogin = async () => {
  if (!form.email || !form.password) {
    errorMessage.value = 'Please fill in all fields'
    return
  }

  loading.value = true
  errorMessage.value = ''

  // Clear previous errors
  errors.email = []
  errors.password = []

  try {
    await authStore.login({
      email: form.email,
      password: form.password
    })

    // Redirect to dashboard on successful login
    router.push({ name: 'dashboard' })

    // Show success notification
    window.showNotification('Login successful!', 'success')

  } catch (error) {
    errorMessage.value = error.message

    // Handle validation errors
    if (error.response?.data?.errors) {
      const validationErrors = error.response.data.errors
      if (validationErrors.email) {
        errors.email = validationErrors.email
      }
      if (validationErrors.password) {
        errors.password = validationErrors.password
      }
    }

  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.v-container {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
