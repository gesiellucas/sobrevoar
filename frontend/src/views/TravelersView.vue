<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <DashboardHeader
      title="Viajantes"
      :user="authStore.user"
      :is-admin="authStore.isAdmin"
      search-placeholder="Buscar por nome ou email..."
      search-mode="text"
      @logout="handleLogout"
      @search="handleSearch"
    />

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filters -->
      <div class="mb-6 flex justify-between items-center">
        <select
          v-model="filterActive"
          @change="loadTravelers"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Todos</option>
          <option value="true">Ativos</option>
          <option value="false">Inativos</option>
        </select>
        <button
          @click="openCreateModal"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Viajante
        </button>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="travelerStore.loading" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-gray-500">Carregando...</p>
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="traveler in travelerStore.travelers" :key="traveler.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium text-sm">
                    {{ traveler.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="ml-3 text-sm font-medium text-gray-900">{{ traveler.name }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ traveler.user?.email || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    traveler.user?.is_admin
                      ? 'bg-purple-100 text-purple-800'
                      : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ traveler.user?.is_admin ? 'Admin' : 'Usuário' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    traveler.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ traveler.is_active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="openEditModal(traveler)"
                  class="text-blue-600 hover:text-blue-900 mr-4"
                >
                  Editar
                </button>
                <button
                  v-if="traveler.is_active"
                  @click="handleDeactivate(traveler)"
                  class="text-red-600 hover:text-red-900"
                >
                  Desativar
                </button>
                <button
                  v-else
                  @click="handleRestore(traveler)"
                  class="text-green-600 hover:text-green-900"
                >
                  Reativar
                </button>
              </td>
            </tr>
            <tr v-if="travelerStore.travelers.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                Nenhum viajante encontrado
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="travelerStore.pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Mostrando {{ travelerStore.travelers.length }} de {{ travelerStore.pagination.total }} viajantes
          </p>
          <div class="flex gap-2">
            <button
              @click="changePage(travelerStore.pagination.current_page - 1)"
              :disabled="travelerStore.pagination.current_page === 1"
              class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              Anterior
            </button>
            <button
              @click="changePage(travelerStore.pagination.current_page + 1)"
              :disabled="travelerStore.pagination.current_page === travelerStore.pagination.last_page"
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
            {{ editingTraveler ? 'Editar Viajante' : 'Novo Viajante' }}
          </h2>
        </div>
        <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Nome completo"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="email@exemplo.com"
            />
          </div>
          <div v-if="!editingTraveler">
            <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
            <input
              v-model="form.password"
              type="password"
              :required="!editingTraveler"
              minlength="8"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Mínimo 8 caracteres"
            />
          </div>
          <div v-if="!editingTraveler">
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha *</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              :required="!editingTraveler"
              minlength="8"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Repita a senha"
            />
          </div>
          <div v-if="editingTraveler" class="space-y-2">
            <p class="text-sm text-gray-500">Deixe em branco para manter a senha atual</p>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
              <input
                v-model="form.password"
                type="password"
                minlength="8"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Nova senha (opcional)"
              />
            </div>
            <div v-if="form.password">
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                minlength="8"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Repita a nova senha"
              />
            </div>
          </div>
          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2">
              <input
                v-model="form.is_admin"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">Administrador</span>
            </label>
            <label class="flex items-center gap-2">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">Ativo</span>
            </label>
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
import { useTravelerStore } from '@/stores/traveler'
import { useAuthStore } from '@/stores/auth'
import { DashboardHeader } from '@/components/dashboard'

const router = useRouter()
const travelerStore = useTravelerStore()
const authStore = useAuthStore()

const search = ref('')
const filterActive = ref('')
const showModal = ref(false)
const editingTraveler = ref(null)
const saving = ref(false)
const formError = ref('')

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  is_admin: false,
  is_active: true,
})

onMounted(() => {
  loadTravelers()
})

async function loadTravelers() {
  const params = {}
  if (search.value) params.search = search.value
  if (filterActive.value !== '') params.is_active = filterActive.value
  await travelerStore.fetchTravelers(1, params)
}

function handleSearch(value) {
  search.value = value
  loadTravelers()
}

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}

function changePage(page) {
  const params = {}
  if (search.value) params.search = search.value
  if (filterActive.value !== '') params.is_active = filterActive.value
  travelerStore.fetchTravelers(page, params)
}

function openCreateModal() {
  editingTraveler.value = null
  form.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    is_admin: false,
    is_active: true,
  }
  formError.value = ''
  showModal.value = true
}

function openEditModal(traveler) {
  editingTraveler.value = traveler
  form.value = {
    name: traveler.name,
    email: traveler.user?.email || '',
    password: '',
    password_confirmation: '',
    is_admin: traveler.user?.is_admin || false,
    is_active: traveler.is_active,
  }
  formError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingTraveler.value = null
}

async function handleSubmit() {
  // Validate password confirmation
  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    formError.value = 'As senhas não coincidem'
    return
  }

  saving.value = true
  formError.value = ''

  try {
    const data = { ...form.value }

    // Remove empty password fields for update
    if (editingTraveler.value && !data.password) {
      delete data.password
      delete data.password_confirmation
    }

    if (editingTraveler.value) {
      await travelerStore.updateTraveler(editingTraveler.value.id, data)
    } else {
      await travelerStore.createTraveler(data)
    }
    closeModal()
    loadTravelers()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Erro ao salvar viajante'
  } finally {
    saving.value = false
  }
}

async function handleDeactivate(traveler) {
  if (confirm(`Tem certeza que deseja desativar o viajante "${traveler.name}"?`)) {
    try {
      await travelerStore.deleteTraveler(traveler.id)
    } catch (error) {
      alert(error.response?.data?.message || 'Erro ao desativar viajante')
    }
  }
}

async function handleRestore(traveler) {
  try {
    await travelerStore.restoreTraveler(traveler.id)
  } catch (error) {
    alert(error.response?.data?.message || 'Erro ao reativar viajante')
  }
}
</script>
