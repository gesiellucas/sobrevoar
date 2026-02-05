import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    console.log('API Request:', config.url, 'Token exists:', !!token)
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Only redirect to login on 401 if not already on auth routes
    if (error.response?.status === 401) {
      const url = error.config?.url || ''
      // Don't redirect if trying to login or refresh
      if (!url.includes('/login') && !url.includes('/refresh')) {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        localStorage.removeItem('token_expires_at')
        // Only redirect if not already on login page
        if (!window.location.pathname.includes('/login')) {
          window.location.href = '/login'
        }
      }
    }
    return Promise.reject(error)
  }
)

export default api
