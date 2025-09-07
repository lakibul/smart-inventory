<template>
  <div style="padding: 40px; text-align: center;">
    <h1 style="color: #28a745;">🎉 Vue.js is Working!</h1>
    <p>This is a test component to verify Vue.js functionality.</p>
    <p>Counter: {{ counter }}</p>
    <button
      @click="counter++"
      style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; margin: 5px;"
    >
      Increment
    </button>
    <button
      @click="counter = 0"
      style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; margin: 5px;"
    >
      Reset
    </button>

    <div style="margin-top: 30px;">
      <router-link to="/login" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">
        Go to Login
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const counter = ref(0)
</script>
