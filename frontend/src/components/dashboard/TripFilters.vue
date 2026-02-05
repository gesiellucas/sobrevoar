<template>
  <div class="card sticky top-8">
    <h3 class="text-lg font-medium mb-4">Filtros</h3>
    <div class="space-y-5">

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
        <VueDatePicker
          v-model="dateRange"
          range
          :format="dateFormat"
          :preview-format="dateFormat"
          :format-locale="datePickerLocale"
          :locale="ptBR"
          :action-row="actionRowConfig"
          select-text="Selecionar"
          cancel-text="Cancelar"
          placeholder="Selecione o período"
          auto-apply
          :enable-time-picker="false"
          @update:model-value="handleDateRangeChange"
        />
        <div
          v-if="tripDuration"
          class="text-xs text-gray-600 bg-gray-100 rounded px-2 py-1 mt-2"
        >
          Duração: {{ tripDuration }}
        </div>
      </div>

      <div v-if="hasActiveFilters" class="pt-2">
        <button
          @click="handleClear"
          class="text-sm text-primary-600 hover:text-primary-700"
        >
          Limpar filtros
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { differenceInDays, format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

// Usando o locale ptBR do date-fns para tradução completa
const datePickerLocale = ptBR

// Tradução dos textos de ação do VueDatePicker
const actionRowConfig = {
  showSelect: true,
  showCancel: true,
  showNow: false,
  showPreview: true
}

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

const dateRange = ref(null)

// Initialize dateRange from modelValue
watch(
  () => [props.modelValue.start_date, props.modelValue.end_date],
  ([start, end]) => {
    if (start && end) {
      dateRange.value = [new Date(start), new Date(end)]
    } else {
      dateRange.value = null
    }
  },
  { immediate: true }
)

const dateFormat = (dates) => {
  if (!dates || !Array.isArray(dates)) return ''
  const [start, end] = dates
  if (!start) return ''

  const formatDate = (d) => {
    const day = String(d.getDate()).padStart(2, '0')
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const year = d.getFullYear()
    return `${day}/${month}/${year}`
  }

  if (!end) return formatDate(start)
  return `${formatDate(start)} - ${formatDate(end)}`
}

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

function handleDateRangeChange(value) {
  if (value && Array.isArray(value) && value.length === 2) {
    const [start, end] = value
    emit('update:modelValue', {
      ...props.modelValue,
      start_date: start ? format(start, 'yyyy-MM-dd') : '',
      end_date: end ? format(end, 'yyyy-MM-dd') : ''
    })
  } else {
    emit('update:modelValue', {
      ...props.modelValue,
      start_date: '',
      end_date: ''
    })
  }
}

function handleClear() {
  dateRange.value = null
  emit('clear')
}

function updateFilter(key, value) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>

<style scoped>
:deep(.dp__input) {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

:deep(.dp__input:focus) {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

:deep(.dp__theme_light) {
  --dp-primary-color: #3b82f6;
  --dp-primary-text-color: #fff;
}
</style>
