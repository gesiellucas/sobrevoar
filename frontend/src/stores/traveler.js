import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

// Cache duration in milliseconds (5 minutes)
const CACHE_DURATION = 5 * 60 * 1000

export const useTravelerStore = defineStore('traveler', () => {
  const travelers = ref([])
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

  const activeTravelers = computed(() =>
    travelers.value.filter(t => t.is_active)
  )

  const travelerOptions = computed(() =>
    activeTravelers.value.map(t => ({
      value: t.id,
      label: t.name,
      email: t.user?.email,
    }))
  )

  function isCacheValid(page, params) {
    if (!lastFetched.value) return false

    const now = Date.now()
    const cacheExpired = now - lastFetched.value > CACHE_DURATION

    const currentParams = JSON.stringify({ page, ...params })
    const paramsChanged = currentParams !== lastFetchParams.value

    return !cacheExpired && !paramsChanged && travelers.value.length > 0
  }

  async function fetchTravelers(page = 1, params = {}, forceRefresh = false) {
    if (!forceRefresh && isCacheValid(page, params)) {
      return { data: travelers.value }
    }

    loading.value = true
    error.value = null

    try {
      const response = await api.get('/travelers', {
        params: { page, ...params },
      })
      travelers.value = response.data.data
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
      error.value = err.response?.data?.message || 'Failed to fetch travelers'
      throw err
    } finally {
      loading.value = false
    }
  }

  function invalidateCache() {
    lastFetched.value = null
    lastFetchParams.value = null
  }

  async function createTraveler(data) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/travelers', data)
      travelers.value.unshift(response.data.data)
      invalidateCache()
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create traveler'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateTraveler(id, data) {
    loading.value = true
    error.value = null

    try {
      const response = await api.put(`/travelers/${id}`, data)
      const index = travelers.value.findIndex(t => t.id === id)
      if (index !== -1) {
        travelers.value[index] = response.data.data
      }
      invalidateCache()
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update traveler'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteTraveler(id) {
    loading.value = true
    error.value = null

    try {
      await api.delete(`/travelers/${id}`)
      const index = travelers.value.findIndex(t => t.id === id)
      if (index !== -1) {
        travelers.value[index].is_active = false
      }
      invalidateCache()
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to deactivate traveler'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function restoreTraveler(id) {
    loading.value = true
    error.value = null

    try {
      const response = await api.patch(`/travelers/${id}/restore`)
      const index = travelers.value.findIndex(t => t.id === id)
      if (index !== -1) {
        travelers.value[index] = response.data.data
      }
      invalidateCache()
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to restore traveler'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    travelers,
    loading,
    error,
    pagination,
    activeTravelers,
    travelerOptions,
    fetchTravelers,
    createTraveler,
    updateTraveler,
    deleteTraveler,
    restoreTraveler,
    invalidateCache,
  }
})
