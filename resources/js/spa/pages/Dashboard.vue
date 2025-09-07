<template>
  <div>
    <!-- Page Header -->
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 font-weight-bold mb-4">Dashboard</h1>
      </v-col>
    </v-row>

    <!-- Statistics Cards -->
    <v-row>
      <v-col cols="12" sm="6" md="3">
        <v-card color="primary" dark>
          <v-card-text>
            <div class="d-flex align-center">
              <v-icon size="48" class="mr-4">mdi-package-variant</v-icon>
              <div>
                <div class="text-h4">{{ stats.total_products || 0 }}</div>
                <div class="text-subtitle1">Total Products</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3" v-if="authStore.hasPermission('warehouse.view')">
        <v-card color="success" dark>
          <v-card-text>
            <div class="d-flex align-center">
              <v-icon size="48" class="mr-4">mdi-warehouse</v-icon>
              <div>
                <div class="text-h4">{{ stats.total_warehouses || 0 }}</div>
                <div class="text-subtitle1">Warehouses</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card color="warning" dark>
          <v-card-text>
            <div class="d-flex align-center">
              <v-icon size="48" class="mr-4">mdi-alert</v-icon>
              <div>
                <div class="text-h4">{{ stats.low_stock_count || 0 }}</div>
                <div class="text-subtitle1">Low Stock</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card color="error" dark>
          <v-card-text>
            <div class="d-flex align-center">
              <v-icon size="48" class="mr-4">mdi-package-down</v-icon>
              <div>
                <div class="text-h4">{{ stats.out_of_stock_count || 0 }}</div>
                <div class="text-subtitle1">Out of Stock</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Stock Value & Recent Activity -->
    <v-row class="mt-4">
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-currency-usd</v-icon>
            Stock Value
          </v-card-title>
          <v-card-text>
            <div class="text-h3 text-primary text-center py-4">
              ${{ formatCurrency(stats.total_stock_value || 0) }}
            </div>
            <v-divider class="my-2"></v-divider>
            <div class="text-caption text-center">
              Total inventory value across all warehouses
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-clock-outline</v-icon>
            Recent Activity
          </v-card-title>
          <v-card-text>
            <div class="text-h3 text-success text-center py-4">
              {{ stats.recent_products || 0 }}
            </div>
            <v-divider class="my-2"></v-divider>
            <div class="text-caption text-center">
              Products added in the last 7 days
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Low Stock Products -->
    <v-row class="mt-4" v-if="stats.low_stock_products && stats.low_stock_products.length > 0">
      <v-col cols="12">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-alert-circle</v-icon>
            Low Stock Alert
          </v-card-title>
          <v-card-text>
            <v-data-table
              :headers="lowStockHeaders"
              :items="stats.low_stock_products"
              density="compact"
              :items-per-page="5"
            >
              <template v-slot:item.current_stock="{ item }">
                <v-chip
                  :color="item.current_stock === 0 ? 'error' : 'warning'"
                  size="small"
                >
                  {{ item.current_stock }}
                </v-chip>
              </template>

              <template v-slot:item.actions="{ item }">
                <v-btn
                  size="small"
                  color="primary"
                  variant="text"
                  :to="{ name: 'products.edit', params: { id: item.id } }"
                >
                  Update Stock
                </v-btn>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Stock by Category Chart -->
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-chart-pie</v-icon>
            Stock Distribution by Category
          </v-card-title>
          <v-card-text>
            <div v-if="chartData.length === 0" class="text-center py-8">
              <v-icon size="64" color="grey">mdi-chart-pie</v-icon>
              <div class="text-h6 text-grey mt-4">No stock data available</div>
            </div>

            <div v-else class="pa-4">
              <v-row>
                <v-col
                  v-for="(item, index) in chartData"
                  :key="index"
                  cols="12"
                  sm="6"
                  md="4"
                  class="d-flex"
                >
                  <v-card class="flex-grow-1" outlined>
                    <v-card-text class="text-center">
                      <div class="text-h4 text-primary">{{ item.stock_count }}</div>
                      <div class="text-subtitle1">{{ item.category }}</div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Actions -->
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-lightning-bolt</v-icon>
            Quick Actions
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12" sm="6" md="3" v-if="authStore.hasPermission('product.create')">
                <v-btn
                  color="primary"
                  size="large"
                  block
                  :to="{ name: 'products.create' }"
                  prepend-icon="mdi-plus"
                >
                  Add Product
                </v-btn>
              </v-col>

              <v-col cols="12" sm="6" md="3">
                <v-btn
                  color="secondary"
                  size="large"
                  block
                  :to="{ name: 'products' }"
                  prepend-icon="mdi-package-variant"
                >
                  View Products
                </v-btn>
              </v-col>

              <v-col cols="12" sm="6" md="3" v-if="authStore.hasPermission('category.create')">
                <v-btn
                  color="success"
                  size="large"
                  block
                  :to="{ name: 'categories' }"
                  prepend-icon="mdi-shape"
                >
                  Manage Categories
                </v-btn>
              </v-col>

              <v-col cols="12" sm="6" md="3" v-if="authStore.hasPermission('warehouse.view')">
                <v-btn
                  color="warning"
                  size="large"
                  block
                  :to="{ name: 'warehouses' }"
                  prepend-icon="mdi-warehouse"
                >
                  Warehouses
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const authStore = useAuthStore()

// Data
const stats = ref({})
const chartData = ref([])
const loading = ref(false)

// Table headers for low stock products
const lowStockHeaders = [
  { title: 'Product Name', key: 'name' },
  { title: 'SKU', key: 'sku' },
  { title: 'Current Stock', key: 'current_stock' },
  { title: 'Reorder Level', key: 'reorder_level' },
  { title: 'Actions', key: 'actions', sortable: false },
]

// Methods
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const fetchDashboardStats = async () => {
  try {
    loading.value = true
    const response = await axios.get('/dashboard/stats')

    if (response.data.success) {
      stats.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching dashboard stats:', error)
    window.showNotification('Failed to load dashboard statistics', 'error')
  } finally {
    loading.value = false
  }
}

const fetchChartData = async () => {
  try {
    const response = await axios.get('/dashboard/stock-chart')

    if (response.data.success) {
      chartData.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching chart data:', error)
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchDashboardStats(),
    fetchChartData()
  ])
})
</script>

<style scoped>
.v-card {
  height: 100%;
}
</style>
