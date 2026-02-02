<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center">
        <router-link :to="{ name: 'dashboard' }" class="text-gray-600 hover:text-gray-900 mr-4">
          ← Back
        </router-link>
        <h1 class="text-2xl font-bold text-gray-900">Trip Request Details</h1>
      </div>
    </header>

    <!-- Main content -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <LoadingSpinner v-if="tripStore.loading" />

      <div v-else-if="tripRequest" class="space-y-6">
        <!-- Trip Info Card -->
        <div class="card">
          <div class="flex justify-between items-start mb-6">
            <div>
              <h2 class="text-lg font-medium text-gray-900">Trip Information</h2>
              <p class="text-sm text-gray-500 mt-1">Request #{{ tripRequest.id }}</p>
            </div>
            <StatusBadge :status="tripRequest.status" />
          </div>

          <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-gray-500">Requester Name</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ tripRequest.requester_name }}</dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Destination</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ tripRequest.destination }}</dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Departure Date</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(tripRequest.departure_date) }}</dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Return Date</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(tripRequest.return_date) }}</dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Created At</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(tripRequest.created_at) }}</dd>
            </div>

            <div>
              <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(tripRequest.updated_at) }}</dd>
            </div>
          </dl>
        </div>

        <!-- User Info Card (for admins) -->
        <div v-if="authStore.isAdmin && tripRequest.user" class="card">
          <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-gray-500">Name</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ tripRequest.user.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Email</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ tripRequest.user.email }}</dd>
            </div>
          </dl>
        </div>

        <!-- Actions Card -->
        <div class="card">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
          <div class="flex space-x-3">
            <button
              v-if="canCancel"
              @click="handleCancel"
              class="btn btn-danger"
            >
              Cancel Trip Request
            </button>

            <button
              v-if="authStore.isAdmin && tripRequest.status !== 'approved'"
              @click="handleUpdateStatus('approved')"
              class="btn bg-green-600 text-white hover:bg-green-700"
            >
              Approve Request
            </button>

            <button
              v-if="authStore.isAdmin && tripRequest.status !== 'cancelled'"
              @click="handleUpdateStatus('cancelled')"
              class="btn btn-danger"
            >
              Reject Request
            </button>
          </div>
        </div>
      </div>

      <div v-else class="card">
        <p class="text-gray-500">Trip request not found.</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTripStore } from '@/stores/trip'
import { format } from 'date-fns'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const tripStore = useTripStore()

const tripRequest = ref(null)

const canCancel = computed(() => {
  if (!tripRequest.value) return false
  return tripRequest.value.status === 'requested' &&
         tripRequest.value.user_id === authStore.user?.id &&
         !authStore.isAdmin
})

onMounted(async () => {
  await loadTripRequest()
})

async function loadTripRequest() {
  try {
    tripRequest.value = await tripStore.fetchTripRequest(route.params.id)
  } catch (error) {
    console.error('Failed to load trip request:', error)
  }
}

async function handleCancel() {
  if (confirm('Are you sure you want to cancel this trip request?')) {
    try {
      await tripStore.deleteTripRequest(tripRequest.value.id)
      router.push({ name: 'dashboard' })
    } catch (error) {
      alert('Failed to cancel trip request')
    }
  }
}

async function handleUpdateStatus(status) {
  const action = status === 'approved' ? 'approve' : 'reject'
  if (confirm(`Are you sure you want to ${action} this trip request?`)) {
    try {
      await tripStore.updateTripRequestStatus(tripRequest.value.id, status)
      await loadTripRequest()
    } catch (error) {
      alert(`Failed to ${action} trip request`)
    }
  }
}

function formatDate(date) {
  return format(new Date(date), 'MMMM dd, yyyy')
}

function formatDateTime(date) {
  return format(new Date(date), 'MMMM dd, yyyy HH:mm')
}
</script>
