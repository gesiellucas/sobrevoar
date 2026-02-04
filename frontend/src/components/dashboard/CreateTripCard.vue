<template>
  <div class="card">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">
      Nova Solicitação de Viagem
    </h2>

    <div v-if="error" class="mb-4 rounded-md bg-red-50 p-4">
      <div class="text-sm text-red-800">{{ error }}</div>
    </div>

    <div v-if="success" class="mb-4 rounded-md bg-green-50 p-4">
      <div class="text-sm text-green-800">
        Solicitação criada com sucesso!
      </div>
    </div>

    <TripForm
      ref="formRef"
      :initial-data="initialData"
      :loading="loading"
      :is-admin="isAdmin"
      submit-label="Criar Solicitação"
      :show-cancel="false"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import TripForm from '@/components/TripForm.vue'

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'success', 'error'])

const formRef = ref(null)
const loading = ref(false)
const error = ref('')
const success = ref(false)

async function handleSubmit(formData) {
  loading.value = true
  error.value = ''
  success.value = false

  try {
    await emit('submit', formData)
    success.value = true
    formRef.value?.resetForm()

    setTimeout(() => {
      success.value = false
    }, 3000)

    emit('success')
  } catch (err) {
    error.value = err.message || 'Falha ao criar solicitação de viagem'
    emit('error', err)
  } finally {
    loading.value = false
  }
}

function resetForm() {
  formRef.value?.resetForm()
  error.value = ''
  success.value = false
}

defineExpose({ resetForm })
</script>
