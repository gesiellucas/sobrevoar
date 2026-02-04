import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

// Cache duration in milliseconds (5 minutes)
const CACHE_DURATION = 5 * 60 * 1000

export const useDestinationStore = defineStore('destination', () => {
  const destinations = ref([])
  const countries = ref([])
  const states = ref([])
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  // Cache control
  const lastFetched = ref(null)
  const lastFetchParams = ref(null)
  const allDestinationsFetched = ref(null)

  const destinationOptions = computed(() =>
    destinations.value.map(d => ({
      value: d.id,
      label: d.full_location,
    }))
  )

  function isCacheValid(page, params) {
    if (!lastFetched.value) return false

    const now = Date.now()
    const cacheExpired = now - lastFetched.value > CACHE_DURATION

    const currentParams = JSON.stringify({ page, ...params })
    const paramsChanged = currentParams !== lastFetchParams.value

    return !cacheExpired && !paramsChanged && destinations.value.length > 0
  }

  async function fetchDestinations(page = 1, params = {}, forceRefresh = false) {
    if (!forceRefresh && isCacheValid(page, params)) {
      return { data: destinations.value }
    }

    loading.value = true
    error.value = null

    try {
      const response = await api.get('/destinations', {
        params: { page, ...params },
      })
      destinations.value = response.data.data
      if (response.data.meta) {
        pagination.value = {
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          per_page: response.data.meta.per_page,
          total: response.data.meta.total,
        }
      }

      lastFetched.value = Date.now()
      lastFetchParams.value = JSON.stringify({ page, ...params })
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch destinations'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchAllDestinations(forceRefresh = false) {
    // Return cached if valid
    if (!forceRefresh && allDestinationsFetched.value &&
        Date.now() - allDestinationsFetched.value < CACHE_DURATION &&
        destinations.value.length > 0) {
      return { data: destinations.value }
    }

    loading.value = true
    error.value = null

    try {
      const response = await api.get('/destinations', {
        params: { all: true },
      })
      destinations.value = response.data.data
      allDestinationsFetched.value = Date.now()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch destinations'
      throw err
    } finally {
      loading.value = false
    }
  }

  function invalidateCache() {
    lastFetched.value = null
    lastFetchParams.value = null
    allDestinationsFetched.value = null
  }

  async function fetchCountries() {
    try {
      const response = await api.get('/destinations/countries')
      countries.value = response.data.data
    } catch (err) {
      console.error('Failed to fetch countries:', err)
    }
  }

  async function fetchStates(country = null) {
    try {
      const params = country ? { country } : {}
      const response = await api.get('/destinations/states', { params })
      states.value = response.data.data
    } catch (err) {
      console.error('Failed to fetch states:', err)
    }
  }

  async function createDestination(data) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/destinations', data)
      destinations.value.unshift(response.data.data)
      invalidateCache()
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create destination'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateDestination(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await api.put(`/destinations/${id}`, data)
      const index = destinations.value.findIndex(d => d.id === id)
      if (index !== -1) {
        destinations.value[index] = response.data.data
      }
      invalidateCache()
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update destination'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteDestination(id) {
    loading.value = true
    error.value = null

    try {
      await api.delete(`/destinations/${id}`)
      destinations.value = destinations.value.filter(d => d.id !== id)
      invalidateCache()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete destination'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    destinations,
    countries,
    states,
    loading,
    error,
    pagination,
    destinationOptions,
    fetchDestinations,
    fetchAllDestinations,
    fetchCountries,
    fetchStates,
    createDestination,
    updateDestination,
    deleteDestination,
    invalidateCache,
  }
})
