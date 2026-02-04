import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/trip-requests/create',
      name: 'trip-request-create',
      component: () => import('@/views/TripRequestCreateView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/trip-requests/:id',
      name: 'trip-request-details',
      component: () => import('@/views/TripRequestDetailsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/destinations',
      name: 'destinations',
      component: () => import('@/views/DestinationsView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/travelers',
      name: 'travelers',
      component: () => import('@/views/TravelersView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    }
  ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Initialize auth from localStorage on first navigation
  if (!authStore.isAuthenticated) {
    authStore.initializeAuth()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
