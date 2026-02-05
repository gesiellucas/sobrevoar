<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    @click.self="close"
  >
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div
        class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
        @click="close"
      ></div>

      <!-- Modal panel -->
      <div
        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
      >
        <!-- Header -->
        <div class="bg-primary-600 px-6 py-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-white">
              Detalhes da Viagem
            </h3>
            <button
              @click="close"
              class="text-white hover:text-gray-200 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="bg-white px-6 py-6">
          <div v-if="trip" class="space-y-4">
            <!-- ID -->
            <div>
              <label class="block text-sm font-medium text-gray-700">ID</label>
              <p class="mt-1 text-sm text-gray-900">{{ trip.id }}</p>
            </div>

            <!-- Viajante -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Viajante</label>
              <p class="mt-1 text-sm text-gray-900">{{ trip.traveler?.name || '-' }}</p>
            </div>

            <!-- Destino -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Destino</label>
              <p class="mt-1 text-sm text-gray-900">{{ trip.destination?.full_location || '-' }}</p>
            </div>

            <!-- Partida -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Partida</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDatetime(trip.departure_datetime) }}</p>
            </div>

            <!-- Retorno -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Retorno</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDatetime(trip.return_datetime) }}</p>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <div class="mt-1">
                <StatusBadge :status="trip.status" />
              </div>
            </div>

            <!-- Descrição (se existir) -->
            <div v-if="trip.description">
              <label class="block text-sm font-medium text-gray-700">Descrição</label>
              <p class="mt-1 text-sm text-gray-900">{{ trip.description }}</p>
            </div>

            <!-- Criado em -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Criado em</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDatetime(trip.created_at) }}</p>
            </div>

            <!-- Atualizado em -->
            <div v-if="trip.updated_at !== trip.created_at">
              <label class="block text-sm font-medium text-gray-700">Atualizado em</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDatetime(trip.updated_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 flex justify-end">
          <button
            @click="close"
            class="btn btn-secondary"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { format, parseISO } from 'date-fns'
import StatusBadge from './StatusBadge.vue'

defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  trip: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

function close() {
  emit('close')
}

function formatDatetime(datetime) {
  if (!datetime) return '-'
  try {
    return format(parseISO(datetime), 'dd/MM/yyyy HH:mm')
  } catch {
    return datetime
  }
}
</script>
