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
      <StatusBadge :status="item.status" />
    </template>

    <template #cell-departure_datetime="{ item }">
      {{ formatDatetime(item.departure_datetime) }}
    </template>

    <template #cell-return_datetime="{ item }">
      {{ formatDatetime(item.return_datetime) }}
    </template>

    <template #actions="{ item }">
      <div class="flex space-x-2">
        <router-link
          :to="{ name: 'trip-request-details', params: { id: item.id } }"
          class="text-primary-600 hover:text-primary-900"
        >
          Ver
        </router-link>

        <button
          v-if="item.status === 'requested' && !isAdmin"
          @click="$emit('cancel', item.id)"
          class="text-red-600 hover:text-red-900"
        >
          Cancelar
        </button>

        <div v-if="isAdmin" class="flex space-x-2">
          <button
            v-if="item.status !== 'approved'"
            @click="$emit('approve', item.id)"
            class="text-green-600 hover:text-green-900"
          >
            Aprovar
          </button>
          <button
            v-if="item.status !== 'cancelled'"
            @click="$emit('reject', item.id)"
            class="text-red-600 hover:text-red-900"
          >
            Rejeitar
          </button>
        </div>
      </div>
    </template>
  </DataTable>
</template>

<script setup>
import { format, parseISO } from 'date-fns'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'

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

function formatDatetime(datetime) {
  if (!datetime) return '-'
  try {
    return format(parseISO(datetime), 'dd/MM/yyyy HH:mm')
  } catch {
    return datetime
  }
}
</script>
