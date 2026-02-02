<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Trip Requests</h1>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-600">
            {{ authStore.user?.name }}
            <span v-if="authStore.isAdmin" class="text-primary-600 font-medium">(Admin)</span>
          </span>
          <button @click="handleLogout" class="btn btn-secondary">
            Logout
          </button>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Actions bar -->
      <div class="mb-6 flex justify-between items-center">
        <router-link :to="{ name: 'trip-request-create' }" class="btn btn-primary">
          + New Trip Request
        </router-link>
      </div>

      <!-- Filters -->
      <div class="card mb-6">
        <h3 class="text-lg font-medium mb-4">Filters</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="filters.status" class="input" @change="applyFilters">
              <option value="">All</option>
              <option value="requested">Requested</option>
              <option value="approved">Approved</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
            <input
              v-model="filters.destination"
              type="text"
              class="input"
              placeholder="Search destination..."
              @input="debounceFilter"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input
              v-model="filters.start_date"
              type="date"
              class="input"
              @change="applyFilters"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input
              v-model="filters.end_date"
              type="date"
              class="input"
              @change="applyFilters"
            />
          </div>
        </div>

        <div v-if="tripStore.hasFilters" class="mt-4">
          <button @click="clearFilters" class="text-sm text-primary-600 hover:text-primary-700">
            Clear all filters
          </button>
        </div>
      </div>

      <!-- Data Table -->
      <DataTable
        :columns="columns"
        :data="tripStore.tripRequests"
        :loading="tripStore.loading"
        :pagination="tripStore.pagination"
        @page-change="handlePageChange"
      >
        <template #cell-status="{ item }">
          <StatusBadge :status="item.status" />
        </template>

        <template #cell-departure_date="{ item }">
          {{ formatDate(item.departure_date) }}
        </template>

        <template #cell-return_date="{ item }">
          {{ formatDate(item.return_date) }}
        </template>

        <template #actions="{ item }">
          <div class="flex space-x-2">
            <router-link
              :to="{ name: 'trip-request-details', params: { id: item.id } }"
              class="text-primary-600 hover:text-primary-900"
            >
              View
            </router-link>

            <button
              v-if="item.status === 'requested' && !authStore.isAdmin"
              @click="handleDelete(item.id)"
              class="text-red-600 hover:text-red-900"
            >
              Cancel
            </button>

            <div v-if="authStore.isAdmin" class="flex space-x-2">
              <button
                v-if="item.status !== 'approved'"
                @click="updateStatus(item.id, 'approved')"
                class="text-green-600 hover:text-green-900"
              >
                Approve
              </button>
              <button
                v-if="item.status !== 'cancelled'"
                @click="updateStatus(item.id, 'cancelled')"
                class="text-red-600 hover:text-red-900"
              >
                Reject
              </button>
            </div>
          </div>
        </template>
      </DataTable>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTripStore } from '@/stores/trip'
import { format } from 'date-fns'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const router = useRouter()
const authStore = useAuthStore()
const tripStore = useTripStore()

const filters = ref({
  status: '',
  destination: '',
  start_date: '',
  end_date: ''
})

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'requester_name', label: 'Requester' },
  { key: 'destination', label: 'Destination' },
  { key: 'departure_date', label: 'Departure' },
  { key: 'return_date', label: 'Return' },
  { key: 'status', label: 'Status' },
]

let debounceTimeout = null

onMounted(() => {
  authStore.initializeAuth()
  loadTripRequests()
})

async function loadTripRequests() {
  await tripStore.fetchTripRequests()
}

function applyFilters() {
  tripStore.setFilters(filters.value)
  loadTripRequests()
}

function debounceFilter() {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

function clearFilters() {
  filters.value = {
    status: '',
    destination: '',
    start_date: '',
    end_date: ''
  }
  tripStore.clearFilters()
  loadTripRequests()
}

function handlePageChange(page) {
  tripStore.fetchTripRequests(page)
}

async function handleDelete(id) {
  if (confirm('Are you sure you want to cancel this trip request?')) {
    try {
      await tripStore.deleteTripRequest(id)
    } catch (error) {
      alert('Failed to cancel trip request')
    }
  }
}

async function updateStatus(id, status) {
  try {
    await tripStore.updateTripRequestStatus(id, status)
  } catch (error) {
    alert('Failed to update status')
  }
}

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}

function formatDate(date) {
  return format(new Date(date), 'MMM dd, yyyy')
}
</script>
