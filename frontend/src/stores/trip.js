import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useTripStore = defineStore('trip', () => {
  const tripRequests = ref([])
  const currentTripRequest = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  })

  const filters = ref({
    status: '',
    destination: '',
    start_date: '',
    end_date: '',
  })

  const hasFilters = computed(() => {
    return !!(filters.value.status || filters.value.destination ||
              filters.value.start_date || filters.value.end_date)
  })

  async function fetchTripRequests(page = 1) {
    loading.value = true
    error.value = null

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
        ...filters.value,
      }

      // Remove empty filters
      Object.keys(params).forEach(key => {
        if (params[key] === '' || params[key] === null) {
          delete params[key]
        }
      })

      const response = await api.get('/trip-requests', { params })
      tripRequests.value = response.data.data

      // Update pagination
      if (response.data.meta) {
        pagination.value = {
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
        }
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch trip requests'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchTripRequest(id) {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/trip-requests/${id}`)
      currentTripRequest.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch trip request'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createTripRequest(data) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/trip-requests', data)
      await fetchTripRequests(pagination.value.currentPage)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create trip request'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateTripRequest(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await api.put(`/trip-requests/${id}`, data)
      await fetchTripRequests(pagination.value.currentPage)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update trip request'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteTripRequest(id) {
    loading.value = true
    error.value = null

    try {
      await api.delete(`/trip-requests/${id}`)
      await fetchTripRequests(pagination.value.currentPage)
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete trip request'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateTripRequestStatus(id, status) {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`/trip-requests/${id}/status`, { status })
      await fetchTripRequests(pagination.value.currentPage)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update status'
      throw err
    } finally {
      loading.value = false
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters }
  }

  function clearFilters() {
    filters.value = {
      status: '',
      destination: '',
      start_date: '',
      end_date: '',
    }
  }

  return {
    tripRequests,
    currentTripRequest,
    loading,
    error,
    pagination,
    filters,
    hasFilters,
    fetchTripRequests,
    fetchTripRequest,
    createTripRequest,
    updateTripRequest,
    deleteTripRequest,
    updateTripRequestStatus,
    setFilters,
    clearFilters,
  }
})
