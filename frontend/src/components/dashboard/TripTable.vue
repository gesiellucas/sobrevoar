<template>
  <DataTable
    :columns="columns"
    :data="data"
    :loading="loading"
    :pagination="pagination"
    @page-change="$emit('page-change', $event)"
  >
    <template #cell-status="{ item }">
      <StatusBadge :status="item.status" />
    </template>

    <template #cell-departure_date="{ item }">
      {{ formatDate(item.departure_date) }}
    </template>

    <template #cell-return_date="{ item }">
      {{ formatDate(item.return_date) }}
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
import { format } from 'date-fns'
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
  { key: 'requester_name', label: 'Solicitante' },
  { key: 'destination', label: 'Destino' },
  { key: 'departure_date', label: 'Partida' },
  { key: 'return_date', label: 'Retorno' },
  { key: 'status', label: 'Status' }
]

function formatDate(date) {
  return format(new Date(date), 'dd/MM/yyyy')
}
</script>
