<template>
  <div>
    <h1 class="text-h4 font-weight-bold mb-4">Product Form</h1>
    <v-card>
      <v-card-text>
        <p>Product form will be implemented here.</p>
        <p v-if="id">Editing product ID: {{ id }}</p>
        <p v-else>Creating new product</p>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  id: String
})
</script>
