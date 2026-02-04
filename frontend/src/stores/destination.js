import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

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

  const destinationOptions = computed(() =>
    destinations.value.map(d => ({
      value: d.id,
      label: d.full_location,
    }))
  )

  async function fetchDestinations(page = 1, params = {}) {
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
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch destinations'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchAllDestinations() {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/destinations', {
        params: { all: true },
      })
      destinations.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch destinations'
      throw err
    } finally {
      loading.value = false
    }
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
  }
})
