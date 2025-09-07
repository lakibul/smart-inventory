<template>
  <div>
    <VRow>
      <VCol cols="12">
        <h4 class="text-h4 mb-6">
          Dashboard
        </h4>
        <p class="mb-0">
          Welcome to the Sneat Vuetify Admin Template integrated with Laravel!
        </p>
      </VCol>
    </VRow>

    <VRow>
      <VCol
        cols="12"
        md="6"
        lg="3"
      >
        <CardStatisticsVertical
          v-bind="{
            title: 'Total Sales',
            subtitle: 'Weekly Project',
            stats: '$25.6k',
            change: 42,
            color: 'primary',
            icon: 'bx-trending-up',
          }"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
        lg="3"
      >
        <CardStatisticsVertical
          v-bind="{
            title: 'Sessions',
            subtitle: 'Last Week',
            stats: '1.28k',
            change: -13,
            color: 'success',
            icon: 'bx-dollar',
          }"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
        lg="3"
      >
        <CardStatisticsVertical
          v-bind="{
            title: 'Total Profit',
            subtitle: 'Last Month',
            stats: '12.5k',
            change: 27,
            color: 'warning',
            icon: 'bx-credit-card',
          }"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
        lg="3"
      >
        <CardStatisticsVertical
          v-bind="{
            title: 'Total Sales',
            subtitle: 'Last Month',
            stats: '4,567',
            change: 13,
            color: 'info',
            icon: 'bx-trending-up',
          }"
        />
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardItem>
            <VCardTitle>Welcome to Laravel + Sneat Template</VCardTitle>
          </VCardItem>
          <VCardText>
            <p>
              You have successfully integrated the Sneat Vuetify template with your Laravel application.
              This template includes:
            </p>
            <ul>
              <li>Modern Vue 3 + Vuetify UI</li>
              <li>Beautiful admin dashboard components</li>
              <li>Dark/Light theme support</li>
              <li>Responsive layout</li>
              <li>Integrated with Laravel Inertia.js</li>
            </ul>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<script setup>
import CardStatisticsVertical from '@core/components/cards/CardStatisticsVertical.vue'

defineOptions({
  layout: 'default',
})
</script>
