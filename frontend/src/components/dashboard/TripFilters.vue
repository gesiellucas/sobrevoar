<template>
  <div class="card sticky top-8">
    <h3 class="text-lg font-medium mb-4">Filtros</h3>
    <div class="space-y-5">
      <!-- Status Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="status in statusOptions"
            :key="status.value"
            type="button"
            @click="toggleStatus(status.value)"
            :class="[
              'px-3 py-1.5 text-sm font-medium rounded-full border transition-colors',
              modelValue.status.includes(status.value)
                ? 'bg-primary-600 text-white border-primary-600'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
            ]"
          >
            {{ status.label }}
          </button>
        </div>
      </div>

      <!-- User Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
        <input
          :value="modelValue.user"
          @input="updateFilter('user', $event.target.value)"
          type="text"
          class="input"
          placeholder="Buscar por usuário..."
        />
      </div>

      <!-- Destination Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Destino</label>
        <input
          :value="modelValue.destination"
          @input="updateFilter('destination', $event.target.value)"
          type="text"
          class="input"
          placeholder="Buscar destino..."
        />
      </div>

      <!-- Date Range Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Período da Viagem</label>
        <div class="space-y-2">
          <div>
            <span class="text-xs text-gray-500">Início</span>
            <input
              :value="modelValue.start_date"
              @change="updateFilter('start_date', $event.target.value)"
              type="date"
              class="input"
            />
          </div>
          <div>
            <span class="text-xs text-gray-500">Fim</span>
            <input
              :value="modelValue.end_date"
              @change="updateFilter('end_date', $event.target.value)"
              type="date"
              class="input"
              :min="modelValue.start_date"
            />
          </div>
          <div
            v-if="tripDuration"
            class="text-xs text-gray-600 bg-gray-100 rounded px-2 py-1"
          >
            Duração: {{ tripDuration }}
          </div>
        </div>
      </div>

      <div v-if="hasActiveFilters" class="pt-2">
        <button
          @click="$emit('clear')"
          class="text-sm text-primary-600 hover:text-primary-700"
        >
          Limpar filtros
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { differenceInDays } from 'date-fns'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  },
  hasActiveFilters: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'clear'])

const statusOptions = [
  { value: 'requested', label: 'Solicitado' },
  { value: 'approved', label: 'Aprovado' },
  { value: 'cancelled', label: 'Cancelado' }
]

const tripDuration = computed(() => {
  if (props.modelValue.start_date && props.modelValue.end_date) {
    const start = new Date(props.modelValue.start_date)
    const end = new Date(props.modelValue.end_date)
    const days = differenceInDays(end, start) + 1
    if (days > 0) {
      return days === 1 ? '1 dia' : `${days} dias`
    }
  }
  return null
})

function toggleStatus(status) {
  const currentStatus = [...props.modelValue.status]
  const index = currentStatus.indexOf(status)

  if (index === -1) {
    currentStatus.push(status)
  } else {
    currentStatus.splice(index, 1)
  }

  emit('update:modelValue', { ...props.modelValue, status: currentStatus })
}

function updateFilter(key, value) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>
