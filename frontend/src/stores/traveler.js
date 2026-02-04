import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

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

  async function fetchTravelers(page = 1, params = {}) {
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
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch travelers'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createTraveler(data) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/travelers', data)
      travelers.value.unshift(response.data.data)
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
  }
})
