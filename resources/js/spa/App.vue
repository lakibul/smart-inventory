<template>
  <v-app>
    <!-- Navigation Drawer -->
    <v-navigation-drawer
      v-if="authStore.isAuthenticated"
      v-model="drawer"
      :rail="rail"
      permanent
      @click="rail = false"
    >
      <v-list-item
        prepend-avatar="https://randomuser.me/api/portraits/men/85.jpg"
        :title="authStore.user?.name || 'User'"
        :subtitle="authStore.user?.email"
        nav
      >
        <template v-slot:append>
          <v-btn
            variant="text"
            icon="mdi-chevron-left"
            @click.stop="rail = !rail"
          ></v-btn>
        </template>
      </v-list-item>

      <v-divider></v-divider>

      <v-list density="compact" nav>
        <v-list-item
          prepend-icon="mdi-view-dashboard"
          title="Dashboard"
          value="dashboard"
          :to="{ name: 'dashboard' }"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-package-variant"
          title="Products"
          value="products"
          :to="{ name: 'products' }"
        ></v-list-item>

        <v-list-item
          prepend-icon="mdi-shape"
          title="Categories"
          value="categories"
          :to="{ name: 'categories' }"
        ></v-list-item>

        <v-list-item
          v-if="authStore.hasPermission('warehouse.view')"
          prepend-icon="mdi-warehouse"
          title="Warehouses"
          value="warehouses"
          :to="{ name: 'warehouses' }"
        ></v-list-item>
      </v-list>
    </v-navigation-drawer>

    <!-- App Bar -->
    <v-app-bar v-if="authStore.isAuthenticated" :order="-1">
      <v-app-bar-nav-icon
        variant="text"
        @click.stop="rail = !rail"
      ></v-app-bar-nav-icon>

      <v-toolbar-title>Smart Inventory</v-toolbar-title>

      <v-spacer></v-spacer>

      <!-- User Menu -->
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-account"></v-btn>
        </template>

        <v-list>
          <v-list-item
            prepend-icon="mdi-account"
            title="Profile"
          ></v-list-item>

          <v-list-item
            prepend-icon="mdi-cog"
            title="Settings"
          ></v-list-item>

          <v-divider></v-divider>

          <v-list-item
            prepend-icon="mdi-logout"
            title="Logout"
            @click="logout"
          ></v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <!-- Main Content -->
    <v-main>
      <v-container fluid>
        <router-view />
      </v-container>
    </v-main>

    <!-- Global Loading Overlay -->
    <v-overlay v-model="loading" class="align-center justify-center">
      <v-progress-circular
        color="primary"
        indeterminate
        size="64"
      ></v-progress-circular>
    </v-overlay>

    <!-- Global Snackbar for Notifications -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="snackbar.timeout"
    >
      {{ snackbar.message }}

      <template v-slot:actions>
        <v-btn
          color="white"
          variant="text"
          @click="snackbar.show = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Navigation state
const drawer = ref(true)
const rail = ref(false)
const loading = ref(false)

// Global snackbar for notifications
const snackbar = reactive({
  show: false,
  message: '',
  color: 'success',
  timeout: 4000
})

// Global event bus for notifications
window.showNotification = (message, color = 'success', timeout = 4000) => {
  snackbar.message = message
  snackbar.color = color
  snackbar.timeout = timeout
  snackbar.show = true
}

// Global loading state
window.setLoading = (state) => {
  loading.value = state
}

// Logout function
const logout = async () => {
  try {
    await authStore.logout()
    router.push({ name: 'login' })
    showNotification('Logged out successfully', 'info')
  } catch (error) {
    showNotification('Error logging out', 'error')
  }
}

// Check authentication on app start
onMounted(async () => {
  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      // Token might be invalid, redirect to login
      router.push({ name: 'login' })
    }
  }
})

// Router guards
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)

  if (requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  } else if (requiresGuest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})
</script>

<style scoped>
.v-navigation-drawer {
  top: 0 !important;
}
</style>
