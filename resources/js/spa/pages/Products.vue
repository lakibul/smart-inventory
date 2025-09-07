<template>
  <div>
    <!-- Page Header -->
    <v-row>
      <v-col cols="12" md="6">
        <h1 class="text-h4 font-weight-bold mb-4">Products</h1>
      </v-col>
      <v-col cols="12" md="6" class="text-right">
        <v-btn
          v-if="authStore.hasPermission('product.create')"
          color="primary"
          :to="{ name: 'products.create' }"
          prepend-icon="mdi-plus"
        >
          Add Product
        </v-btn>
      </v-col>
    </v-row>

    <!-- Filters -->
    <v-row>
      <v-col cols="12" md="4">
        <v-text-field
          v-model="search"
          label="Search products..."
          prepend-inner-icon="mdi-magnify"
          clearable
          @input="debouncedSearch"
        ></v-text-field>
      </v-col>

      <v-col cols="12" md="3">
        <v-select
          v-model="selectedCategory"
          :items="categories"
          item-title="name"
          item-value="id"
          label="Category"
          clearable
          @update:model-value="fetchProducts"
        ></v-select>
      </v-col>

      <v-col cols="12" md="3">
        <v-select
          v-model="selectedStatus"
          :items="statusOptions"
          label="Status"
          clearable
          @update:model-value="fetchProducts"
        ></v-select>
      </v-col>

      <v-col cols="12" md="2">
        <v-btn
          color="secondary"
          block
          @click="resetFilters"
          prepend-icon="mdi-refresh"
        >
          Reset
        </v-btn>
      </v-col>
    </v-row>

    <!-- Products Table -->
    <v-card>
      <v-card-text>
        <v-data-table-server
          v-model:items-per-page="itemsPerPage"
          v-model:page="page"
          :headers="headers"
          :items="products"
          :items-length="totalItems"
          :loading="loading"
          class="elevation-1"
          @update:options="handleOptionsUpdate"
        >
          <template v-slot:item.name="{ item }">
            <div>
              <div class="font-weight-medium">{{ item.name }}</div>
              <div class="text-caption text-grey">{{ item.sku }}</div>
            </div>
          </template>

          <template v-slot:item.category="{ item }">
            <v-chip
              v-if="item.category"
              size="small"
              color="primary"
              variant="outlined"
            >
              {{ item.category.name }}
            </v-chip>
          </template>

          <template v-slot:item.cost_price="{ item }">
            ${{ formatCurrency(item.cost_price) }}
          </template>

          <template v-slot:item.sell_price="{ item }">
            ${{ formatCurrency(item.sell_price) }}
          </template>

          <template v-slot:item.total_stock="{ item }">
            <v-chip
              :color="getStockColor(item)"
              size="small"
            >
              {{ item.total_stock }}
            </v-chip>
          </template>

          <template v-slot:item.status="{ item }">
            <v-chip
              :color="item.status === 'active' ? 'success' : 'error'"
              size="small"
            >
              {{ item.status }}
            </v-chip>
          </template>

          <template v-slot:item.actions="{ item }">
            <v-btn
              size="small"
              color="primary"
              variant="text"
              icon="mdi-eye"
              @click="viewProduct(item)"
            ></v-btn>

            <v-btn
              v-if="authStore.hasPermission('product.edit')"
              size="small"
              color="secondary"
              variant="text"
              icon="mdi-pencil"
              :to="{ name: 'products.edit', params: { id: item.id } }"
            ></v-btn>

            <v-btn
              v-if="authStore.hasPermission('product.delete')"
              size="small"
              color="error"
              variant="text"
              icon="mdi-delete"
              @click="confirmDelete(item)"
            ></v-btn>
          </template>
        </v-data-table-server>
      </v-card-text>
    </v-card>

    <!-- Product Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="800">
      <v-card v-if="selectedProduct">
        <v-card-title>
          <span class="text-h5">{{ selectedProduct.name }}</span>
        </v-card-title>

        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <v-list density="compact">
                <v-list-item>
                  <v-list-item-title>SKU</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedProduct.sku }}</v-list-item-subtitle>
                </v-list-item>

                <v-list-item>
                  <v-list-item-title>Category</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedProduct.category?.name || 'N/A' }}</v-list-item-subtitle>
                </v-list-item>

                <v-list-item>
                  <v-list-item-title>Unit</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedProduct.unit }}</v-list-item-subtitle>
                </v-list-item>

                <v-list-item>
                  <v-list-item-title>Cost Price</v-list-item-title>
                  <v-list-item-subtitle>${{ formatCurrency(selectedProduct.cost_price) }}</v-list-item-subtitle>
                </v-list-item>

                <v-list-item>
                  <v-list-item-title>Sell Price</v-list-item-title>
                  <v-list-item-subtitle>${{ formatCurrency(selectedProduct.sell_price) }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-col>

            <v-col cols="12" md="6">
              <v-list density="compact">
                <v-list-item>
                  <v-list-item-title>Total Stock</v-list-item-title>
                  <v-list-item-subtitle>
                    <v-chip :color="getStockColor(selectedProduct)" size="small">
                      {{ selectedProduct.total_stock }}
                    </v-chip>
                  </v-list-item-subtitle>
                </v-list-item>

                <v-list-item>
                  <v-list-item-title>Reorder Level</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedProduct.reorder_level }}</v-list-item-subtitle>
                </v-list-item>

                <v-list-item>
                  <v-list-item-title>Status</v-list-item-title>
                  <v-list-item-subtitle>
                    <v-chip
                      :color="selectedProduct.status === 'active' ? 'success' : 'error'"
                      size="small"
                    >
                      {{ selectedProduct.status }}
                    </v-chip>
                  </v-list-item-subtitle>
                </v-list-item>

                <v-list-item v-if="selectedProduct.barcode">
                  <v-list-item-title>Barcode</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedProduct.barcode }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-col>
          </v-row>

          <v-divider class="my-4"></v-divider>

          <div v-if="selectedProduct.description">
            <h3 class="text-h6 mb-2">Description</h3>
            <p>{{ selectedProduct.description }}</p>
          </div>

          <div v-if="selectedProduct.stock_levels && selectedProduct.stock_levels.length > 0">
            <h3 class="text-h6 mb-2">Stock by Warehouse</h3>
            <v-chip
              v-for="stock in selectedProduct.stock_levels"
              :key="stock.warehouse.id"
              class="ma-1"
              color="info"
              size="small"
            >
              {{ stock.warehouse.name }}: {{ stock.available_qty }}
            </v-chip>
          </div>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="detailsDialog = false">Close</v-btn>
          <v-btn
            v-if="authStore.hasPermission('product.edit')"
            color="primary"
            :to="{ name: 'products.edit', params: { id: selectedProduct.id } }"
          >
            Edit Product
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>
          Are you sure you want to delete "{{ productToDelete?.name }}"?
          This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="deleteProduct" :loading="deleting">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const authStore = useAuthStore()

// Data
const products = ref([])
const categories = ref([])
const loading = ref(false)
const deleting = ref(false)
const totalItems = ref(0)
const page = ref(1)
const itemsPerPage = ref(15)

// Filters
const search = ref('')
const selectedCategory = ref(null)
const selectedStatus = ref(null)

// Dialogs
const detailsDialog = ref(false)
const deleteDialog = ref(false)
const selectedProduct = ref(null)
const productToDelete = ref(null)

// Options
const statusOptions = [
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' }
]

// Table headers
const headers = [
  { title: 'Product', key: 'name', sortable: true },
  { title: 'Category', key: 'category', sortable: false },
  { title: 'Cost Price', key: 'cost_price', sortable: true },
  { title: 'Sell Price', key: 'sell_price', sortable: true },
  { title: 'Stock', key: 'total_stock', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
]

// Methods
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value || 0)
}

const getStockColor = (product) => {
  if (product.total_stock === 0) return 'error'
  if (product.is_low_stock) return 'warning'
  return 'success'
}

const fetchProducts = async () => {
  try {
    loading.value = true

    const params = {
      page: page.value,
      per_page: itemsPerPage.value,
    }

    if (search.value) params.search = search.value
    if (selectedCategory.value) params.category_id = selectedCategory.value
    if (selectedStatus.value) params.status = selectedStatus.value

    const response = await axios.get('/products', { params })

    if (response.data.success) {
      const data = response.data.data
      products.value = data.data
      totalItems.value = data.total
    }
  } catch (error) {
    console.error('Error fetching products:', error)
    window.showNotification('Failed to load products', 'error')
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await axios.get('/categories')
    if (response.data.success) {
      categories.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
}

const handleOptionsUpdate = (options) => {
  page.value = options.page
  itemsPerPage.value = options.itemsPerPage
  fetchProducts()
}

// Debounced search
let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    fetchProducts()
  }, 500)
}

const resetFilters = () => {
  search.value = ''
  selectedCategory.value = null
  selectedStatus.value = null
  page.value = 1
  fetchProducts()
}

const viewProduct = async (product) => {
  try {
    const response = await axios.get(`/products/${product.id}`)
    if (response.data.success) {
      selectedProduct.value = response.data.data
      detailsDialog.value = true
    }
  } catch (error) {
    console.error('Error fetching product details:', error)
    window.showNotification('Failed to load product details', 'error')
  }
}

const confirmDelete = (product) => {
  productToDelete.value = product
  deleteDialog.value = true
}

const deleteProduct = async () => {
  try {
    deleting.value = true

    const response = await axios.delete(`/products/${productToDelete.value.id}`)

    if (response.data.success) {
      window.showNotification('Product deleted successfully', 'success')
      deleteDialog.value = false
      fetchProducts()
    }
  } catch (error) {
    console.error('Error deleting product:', error)
    const message = error.response?.data?.message || 'Failed to delete product'
    window.showNotification(message, 'error')
  } finally {
    deleting.value = false
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchProducts(),
    fetchCategories()
  ])
})
</script>
