<template>
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-6">
          <h2 class="text-2xl font-bold text-gray-900">{{ title }}</h2>

          <!-- Search -->
          <div class="flex items-center gap-2">
            <div class="relative">
              <input
                v-model="searchValue"
                type="text"
                :placeholder="searchPlaceholder"
                :disabled="searchLoading"
                :class="[
                  'px-3 py-1.5 pl-9 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed',
                  searchMode === 'text' ? 'w-64' : 'w-40'
                ]"
                @keyup.enter="handleSearchEnter"
                @input="handleSearchInput"
              />
              <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <button
                v-if="searchValue && !searchLoading"
                @click="clearSearch"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <!-- Loading spinner -->
              <div
                v-if="searchLoading"
                class="absolute right-2 top-1/2 -translate-y-1/2"
              >
                <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Admin Navigation -->
          <nav v-if="isAdmin" class="flex items-center gap-1">
            <router-link
              to="/dashboard"
              class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="[
                $route.path === '/dashboard'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              Viagens
            </router-link>
            <router-link
              to="/destinations"
              class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="[
                $route.path === '/destinations'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              Destinos
            </router-link>
            <router-link
              to="/travelers"
              class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
              :class="[
                $route.path === '/travelers'
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
              ]"
            >
              Viajantes
            </router-link>
          </nav>
        </div>

        <section class="flex items-center gap-6">
          <!-- Stats row -->
          <div class="flex items-center gap-6">
            <div
              v-for="(stat, index) in stats"
              :key="index"
              class="border-t-2 border-gray-200 px-2 flex items-center flex-col"
            >
              <span class="text-xs text-gray-600">{{ stat.label }}:</span>
              <span class="text-xl font-bold text-gray-900">{{ stat.value }}</span>
            </div>
          </div>

          <!-- User Dropdown -->
          <div class="relative">
            <button
              @click="dropdownOpen = !dropdownOpen"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <div
                class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white font-medium text-sm"
              >
                {{ userInitials }}
              </div>
              <div class="text-left hidden sm:block">
                <div class="text-sm font-medium text-gray-900">
                  {{ user?.name }}
                </div>
                <div v-if="isAdmin" class="text-xs text-primary-600">
                  Admin
                </div>
              </div>
              <svg
                class="w-4 h-4 text-gray-500 transition-transform"
                :class="{ 'rotate-180': dropdownOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="dropdownOpen"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
            >
              <div class="px-4 py-2 border-b border-gray-100">
                <div class="text-sm font-medium text-gray-900">
                  {{ user?.name }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ user?.email }}
                </div>
              </div>
              <!-- Admin Links in Dropdown (mobile) -->
              <div v-if="isAdmin" class="sm:hidden border-b border-gray-100">
                <router-link
                  to="/destinations"
                  @click="dropdownOpen = false"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                  Gerenciar Destinos
                </router-link>
                <router-link
                  to="/travelers"
                  @click="dropdownOpen = false"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                  Gerenciar Viajantes
                </router-link>
              </div>
              <button
                @click="handleLogout"
                class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                  />
                </svg>
                Sair
              </button>
            </div>

            <!-- Backdrop to close dropdown -->
            <div
              v-if="dropdownOpen"
              class="fixed inset-0 z-40"
              @click="dropdownOpen = false"
            ></div>
          </div>
        </section>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  },
  stats: {
    type: Array,
    default: () => []
  },
  user: {
    type: Object,
    default: null
  },
  isAdmin: {
    type: Boolean,
    default: false
  },
  searchLoading: {
    type: Boolean,
    default: false
  },
  searchPlaceholder: {
    type: String,
    default: 'Buscar por ID...'
  },
  searchMode: {
    type: String,
    default: 'id', // 'id' ou 'text'
    validator: (value) => ['id', 'text'].includes(value)
  }
})

const emit = defineEmits(['logout', 'search'])

const dropdownOpen = ref(false)
const searchValue = ref('')
let debounceTimeout = null

function handleSearchEnter() {
  if (props.searchMode === 'id' && searchValue.value.trim()) {
    emit('search', searchValue.value.trim())
  }
}

function handleSearchInput() {
  if (props.searchMode === 'text') {
    clearTimeout(debounceTimeout)
    debounceTimeout = setTimeout(() => {
      emit('search', searchValue.value.trim())
    }, 300)
  }
}

function clearSearch() {
  searchValue.value = ''
  emit('search', props.searchMode === 'id' ? null : '')
}

const userInitials = computed(() => {
  const name = props.user?.name || ''
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

function handleLogout() {
  dropdownOpen.value = false
  emit('logout')
}
</script>
