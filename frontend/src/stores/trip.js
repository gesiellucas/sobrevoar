import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

// Cache duration in milliseconds (5 minutes)
const CACHE_DURATION = 5 * 60 * 1000

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

  // Cache control
  const lastFetched = ref(null)
  const lastFetchParams = ref(null)
  const tripRequestCache = ref({}) // Cache for individual trip requests by ID

  const hasFilters = computed(() => {
    return !!(filters.value.status || filters.value.destination ||
              filters.value.start_date || filters.value.end_date)
  })

  function isCacheValid(page) {
    if (!lastFetched.value) return false

    const now = Date.now()
    const cacheExpired = now - lastFetched.value > CACHE_DURATION

    // Check if params changed
    const currentParams = JSON.stringify({ page, ...filters.value })
    const paramsChanged = currentParams !== lastFetchParams.value

    return !cacheExpired && !paramsChanged && tripRequests.value.length > 0
  }

  async function fetchTripRequests(page = 1, forceRefresh = false) {
    // Return cached data if valid
    if (!forceRefresh && isCacheValid(page)) {
      return { data: tripRequests.value }
    }

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

      // Update cache timestamp and params
      lastFetched.value = Date.now()
      lastFetchParams.value = JSON.stringify({ page, ...filters.value })

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch trip requests'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchTripRequest(id, forceRefresh = false) {
    // Check cache for individual trip request
    const cached = tripRequestCache.value[id]
    if (!forceRefresh && cached && Date.now() - cached.timestamp < CACHE_DURATION) {
      currentTripRequest.value = cached.data
      return cached.data
    }

    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/trip-requests/${id}`)
      currentTripRequest.value = response.data.data

      // Cache the result
      tripRequestCache.value[id] = {
        data: response.data.data,
        timestamp: Date.now()
      }

      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch trip request'
      throw err
    } finally {
      loading.value = false
    }
  }

  function invalidateCache() {
    lastFetched.value = null
    lastFetchParams.value = null
    tripRequestCache.value = {}
  }

  async function createTripRequest(data) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/trip-requests', data)
      invalidateCache()
      await fetchTripRequests(pagination.value.currentPage, true)
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
      invalidateCache()
      await fetchTripRequests(pagination.value.currentPage, true)
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
      invalidateCache()
      await fetchTripRequests(pagination.value.currentPage, true)
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
      invalidateCache()
      await fetchTripRequests(pagination.value.currentPage, true)
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
    invalidateCache,
  }
})
