<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <DashboardHeader
      title="Destinos"
      :user="authStore.user"
      :is-admin="authStore.isAdmin"
      search-placeholder="Buscar por cidade, estado ou país..."
      search-mode="text"
      @logout="handleLogout"
      @search="handleSearch"
    />

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Action button -->
      <div class="mb-6 flex justify-end">
        <button
          @click="openCreateModal"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Destino
        </button>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="destinationStore.loading" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-gray-500">Carregando...</p>
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cidade</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">País</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="destination in destinationStore.destinations" :key="destination.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ destination.city }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ destination.state || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ destination.country }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="openEditModal(destination)"
                  class="text-blue-600 hover:text-blue-900 mr-4"
                >
                  Editar
                </button>
                <button
                  @click="handleDelete(destination)"
                  class="text-red-600 hover:text-red-900"
                >
                  Excluir
                </button>
              </td>
            </tr>
            <tr v-if="destinationStore.destinations.length === 0">
              <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                Nenhum destino encontrado
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="destinationStore.pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Mostrando {{ destinationStore.destinations.length }} de {{ destinationStore.pagination.total }} destinos
          </p>
          <div class="flex gap-2">
            <button
              @click="changePage(destinationStore.pagination.current_page - 1)"
              :disabled="destinationStore.pagination.current_page === 1"
              class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              Anterior
            </button>
            <button
              @click="changePage(destinationStore.pagination.current_page + 1)"
              :disabled="destinationStore.pagination.current_page === destinationStore.pagination.last_page"
              class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              Próximo
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">
            {{ editingDestination ? 'Editar Destino' : 'Novo Destino' }}
          </h2>
        </div>
        <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
            <input
              v-model="form.city"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Ex: São Paulo"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <input
              v-model="form.state"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Ex: SP"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">País *</label>
            <input
              v-model="form.country"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Ex: Brasil"
            />
          </div>
          <div v-if="formError" class="text-red-600 text-sm">
            {{ formError }}
          </div>
          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {{ saving ? 'Salvando...' : 'Salvar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useDestinationStore } from '@/stores/destination'
import { useAuthStore } from '@/stores/auth'
import { DashboardHeader } from '@/components/dashboard'

const router = useRouter()
const destinationStore = useDestinationStore()
const authStore = useAuthStore()

const search = ref('')
const showModal = ref(false)
const editingDestination = ref(null)
const saving = ref(false)
const formError = ref('')

const form = ref({
  city: '',
  state: '',
  country: '',
})

onMounted(() => {
  loadDestinations()
})

async function loadDestinations() {
  await destinationStore.fetchDestinations(1, search.value ? { search: search.value } : {})
}

function handleSearch(value) {
  search.value = value
  loadDestinations()
}

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}

function changePage(page) {
  destinationStore.fetchDestinations(page, search.value ? { search: search.value } : {})
}

function openCreateModal() {
  editingDestination.value = null
  form.value = { city: '', state: '', country: '' }
  formError.value = ''
  showModal.value = true
}

function openEditModal(destination) {
  editingDestination.value = destination
  form.value = {
    city: destination.city,
    state: destination.state || '',
    country: destination.country,
  }
  formError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingDestination.value = null
}

async function handleSubmit() {
  saving.value = true
  formError.value = ''

  try {
    if (editingDestination.value) {
      await destinationStore.updateDestination(editingDestination.value.id, form.value)
    } else {
      await destinationStore.createDestination(form.value)
    }
    closeModal()
    loadDestinations()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Erro ao salvar destino'
  } finally {
    saving.value = false
  }
}

async function handleDelete(destination) {
  if (confirm(`Tem certeza que deseja excluir o destino "${destination.full_location}"?`)) {
    try {
      await destinationStore.deleteDestination(destination.id)
    } catch (error) {
      alert(error.response?.data?.message || 'Erro ao excluir destino')
    }
  }
}
</script>
