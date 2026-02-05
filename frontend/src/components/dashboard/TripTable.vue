<template>
  <DataTable
    :columns="columns"
    :data="data"
    :loading="loading"
    :pagination="pagination"
    @page-change="$emit('page-change', $event)"
  >
    <template #cell-traveler="{ item }">
      {{ item.traveler?.name || '-' }}
    </template>

    <template #cell-destination="{ item }">
      {{ item.destination?.full_location || '-' }}
    </template>

    <template #cell-status="{ item }">
      <!-- Se status for requested e for admin, mostrar botões de aprovar/rejeitar -->
      <div v-if="item.status === 'requested' && isAdmin" class="flex space-x-2">
        <button
          @click="$emit('approve', item.id)"
          class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 transition-colors"
        >
          Aprovar
        </button>
        <button
          @click="$emit('reject', item.id)"
          class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition-colors"
        >
          Rejeitar
        </button>
      </div>
      <!-- Caso contrário, mostrar o badge normal -->
      <StatusBadge v-else :status="item.status" />
    </template>

    <template #cell-departure_datetime="{ item }">
      {{ formatDatetime(item.departure_datetime) }}
    </template>

    <template #cell-return_datetime="{ item }">
      {{ formatDatetime(item.return_datetime) }}
    </template>

    <template #actions="{ item }">
      <div class="flex space-x-2">
        <button
          @click="openModal(item)"
          class="text-primary-600 hover:text-primary-900"
        >
          Ver
        </button>

        <!-- Só permite cancelar se não for aprovado -->
        <button
          v-if="item.status !== 'approved' && !isAdmin"
          @click="$emit('cancel', item.id)"
          class="text-red-600 hover:text-red-900"
        >
          Cancelar
        </button>
      </div>
    </template>
  </DataTable>

  <!-- Modal de detalhes -->
  <TripDetailsModal
    :is-open="isModalOpen"
    :trip="selectedTrip"
    @close="closeModal"
  />
</template>

<script setup>
import { ref } from 'vue'
import { format, parseISO } from 'date-fns'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import TripDetailsModal from '@/components/TripDetailsModal.vue'

defineProps({
  data: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  pagination: {
    type: Object,
    default: null
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
})

defineEmits(['page-change', 'cancel', 'approve', 'reject'])

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'traveler', label: 'Viajante' },
  { key: 'destination', label: 'Destino' },
  { key: 'departure_datetime', label: 'Partida' },
  { key: 'return_datetime', label: 'Retorno' },
  { key: 'status', label: 'Status' }
]

// Modal state
const isModalOpen = ref(false)
const selectedTrip = ref(null)

function openModal(trip) {
  selectedTrip.value = trip
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  selectedTrip.value = null
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
