<template>
  <span
    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
    :class="badgeClass"
  >
    {{ statusText }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (value) => ['requested', 'approved', 'cancelled'].includes(value)
  }
})

const badgeClass = computed(() => {
  const classes = {
    requested: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[props.status]
})

const statusText = computed(() => {
  const translations = {
    requested: 'Solicitado',
    approved: 'Aprovado',
    cancelled: 'Cancelado'
  }
  return translations[props.status] || props.status
})
</script>
