<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div>
      <label for="requester_name" class="block text-sm font-medium text-gray-700">
        Requester Name
      </label>
      <input
        id="requester_name"
        v-model="formData.requester_name"
        type="text"
        required
        class="input mt-1"
        :class="{ 'border-red-500': errors.requester_name }"
      />
      <p v-if="errors.requester_name" class="mt-1 text-sm text-red-600">
        {{ errors.requester_name }}
      </p>
    </div>

    <div>
      <label for="destination" class="block text-sm font-medium text-gray-700">
        Destination
      </label>
      <input
        id="destination"
        v-model="formData.destination"
        type="text"
        required
        class="input mt-1"
        :class="{ 'border-red-500': errors.destination }"
        placeholder="e.g., Paris, France"
      />
      <p v-if="errors.destination" class="mt-1 text-sm text-red-600">
        {{ errors.destination }}
      </p>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
      <div>
        <label for="departure_date" class="block text-sm font-medium text-gray-700">
          Departure Date
        </label>
        <input
          id="departure_date"
          v-model="formData.departure_date"
          type="date"
          required
          :min="minDate"
          class="input mt-1"
          :class="{ 'border-red-500': errors.departure_date }"
        />
        <p v-if="errors.departure_date" class="mt-1 text-sm text-red-600">
          {{ errors.departure_date }}
        </p>
      </div>

      <div>
        <label for="return_date" class="block text-sm font-medium text-gray-700">
          Return Date
        </label>
        <input
          id="return_date"
          v-model="formData.return_date"
          type="date"
          required
          :min="formData.departure_date || minDate"
          class="input mt-1"
          :class="{ 'border-red-500': errors.return_date }"
        />
        <p v-if="errors.return_date" class="mt-1 text-sm text-red-600">
          {{ errors.return_date }}
        </p>
      </div>
    </div>

    <div class="flex justify-end space-x-3">
      <button
        v-if="showCancel"
        type="button"
        @click="$emit('cancel')"
        class="btn btn-secondary"
      >
        Cancel
      </button>
      <button
        type="submit"
        :disabled="loading"
        class="btn btn-primary"
      >
        {{ loading ? 'Submitting...' : submitLabel }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { format, addDays } from 'date-fns'

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  },
  loading: {
    type: Boolean,
    default: false
  },
  submitLabel: {
    type: String,
    default: 'Submit'
  },
  showCancel: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['submit', 'cancel'])

const formData = ref({
  requester_name: props.initialData.requester_name || '',
  destination: props.initialData.destination || '',
  departure_date: props.initialData.departure_date || '',
  return_date: props.initialData.return_date || '',
})

const errors = ref({})

const minDate = computed(() => {
  return format(addDays(new Date(), 1), 'yyyy-MM-dd')
})

watch(() => props.initialData, (newData) => {
  if (newData) {
    formData.value = {
      requester_name: newData.requester_name || '',
      destination: newData.destination || '',
      departure_date: newData.departure_date || '',
      return_date: newData.return_date || '',
    }
  }
}, { deep: true })

function validateForm() {
  errors.value = {}

  if (!formData.value.requester_name) {
    errors.value.requester_name = 'Requester name is required'
  }

  if (!formData.value.destination) {
    errors.value.destination = 'Destination is required'
  }

  if (!formData.value.departure_date) {
    errors.value.departure_date = 'Departure date is required'
  }

  if (!formData.value.return_date) {
    errors.value.return_date = 'Return date is required'
  }

  if (formData.value.departure_date && formData.value.return_date) {
    if (new Date(formData.value.return_date) <= new Date(formData.value.departure_date)) {
      errors.value.return_date = 'Return date must be after departure date'
    }
  }

  return Object.keys(errors.value).length === 0
}

function handleSubmit() {
  if (validateForm()) {
    emit('submit', { ...formData.value })
  }
}
</script>
