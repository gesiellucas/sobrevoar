<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center">
        <router-link :to="{ name: 'dashboard' }" class="text-gray-600 hover:text-gray-900 mr-4">
          ← Back
        </router-link>
        <h1 class="text-2xl font-bold text-gray-900">Create Trip Request</h1>
      </div>
    </header>

    <!-- Main content -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="card">
        <h2 class="text-lg font-medium mb-6">Trip Details</h2>

        <div v-if="error" class="mb-4 rounded-md bg-red-50 p-4">
          <div class="text-sm text-red-800">
            {{ error }}
          </div>
        </div>

        <TripForm
          :initial-data="{ requester_name: authStore.user?.name }"
          :loading="loading"
          submit-label="Create Trip Request"
          @submit="handleSubmit"
          @cancel="handleCancel"
        />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTripStore } from '@/stores/trip'
import TripForm from '@/components/TripForm.vue'

const router = useRouter()
const authStore = useAuthStore()
const tripStore = useTripStore()

const loading = ref(false)
const error = ref('')

async function handleSubmit(formData) {
  loading.value = true
  error.value = ''

  try {
    await tripStore.createTripRequest(formData)
    router.push({ name: 'dashboard' })
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create trip request'
  } finally {
    loading.value = false
  }
}

function handleCancel() {
  router.push({ name: 'dashboard' })
}
</script>
