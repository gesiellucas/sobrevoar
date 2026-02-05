import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(null)
  const expiresAt = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.is_admin || false)

  function setUser(userData) {
    user.value = userData
    if (userData) {
      localStorage.setItem('user', JSON.stringify(userData))
    } else {
      localStorage.removeItem('user')
    }
  }

  function setToken(tokenValue, expiresIn = null) {
    token.value = tokenValue
    if (tokenValue) {
      localStorage.setItem('token', tokenValue)
      if (expiresIn) {
        const expiry = Date.now() + expiresIn * 1000
        expiresAt.value = expiry
        localStorage.setItem('token_expires_at', expiry.toString())
      }
    } else {
      localStorage.removeItem('token')
      localStorage.removeItem('token_expires_at')
      expiresAt.value = null
    }
  }

  function isTokenExpired() {
    if (!expiresAt.value) return true
    // Consider token expired 60 seconds before actual expiry
    return Date.now() >= expiresAt.value - 60000
  }

  async function login(credentials) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/login', credentials)
      const { user: userData, access_token, expires_in } = response.data

      setUser(userData)
      setToken(access_token, expires_in)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function register(userData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/register', userData)
      const { user: newUser, access_token, expires_in } = response.data

      setUser(newUser)
      setToken(access_token, expires_in)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true

    try {
      await api.post('/logout')
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      setUser(null)
      setToken(null)
      loading.value = false
    }
  }

  async function refreshToken() {
    try {
      const response = await api.post('/refresh')
      const { user: userData, access_token, expires_in } = response.data

      setUser(userData)
      setToken(access_token, expires_in)

      return access_token
    } catch (err) {
      console.error('Token refresh failed:', err)
      setUser(null)
      setToken(null)
      throw err
    }
  }

  async function fetchUser() {
    try {
      const response = await api.get('/user')
      setUser(response.data.user)
    } catch (err) {
      console.error('Fetch user error:', err)
      setUser(null)
      setToken(null)
    }
  }

  function initializeAuth() {
    const storedToken = localStorage.getItem('token')
    const storedUser = localStorage.getItem('user')
    const storedExpiry = localStorage.getItem('token_expires_at')

    if (storedToken && storedUser) {
      token.value = storedToken
      user.value = JSON.parse(storedUser)
      expiresAt.value = storedExpiry ? parseInt(storedExpiry) : null
    }
  }

  function clearAuth() {
    setUser(null)
    setToken(null)
  }

  return {
    user,
    token,
    expiresAt,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    isTokenExpired,
    login,
    register,
    logout,
    refreshToken,
    fetchUser,
    initializeAuth,
    clearAuth,
  }
})
