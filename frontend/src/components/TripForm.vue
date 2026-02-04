<template>
  <form @submit.prevent="handleSubmit" class="grid grid-cols-12 gap-6">
    <!-- Traveler selector (admin only) -->
    <div v-if="isAdmin" class="col-span-3">
      <label for="traveler_id" class="block text-sm font-medium text-gray-700">
        Viajante
      </label>
      <select
        id="traveler_id"
        v-model="formData.traveler_id"
        required
        class="input mt-1"
        :class="{ 'border-red-500': errors.traveler_id }"
      >
        <option value="">Selecione um viajante</option>
        <option
          v-for="traveler in travelers"
          :key="traveler.value"
          :value="traveler.value"
        >
          {{ traveler.label }} ({{ traveler.email }})
        </option>
      </select>
      <p v-if="errors.traveler_id" class="mt-1 text-sm text-red-600">
        {{ errors.traveler_id }}
      </p>
    </div>

    <div :class="isAdmin ? 'col-span-3' : 'col-span-4'">
      <label for="destination_id" class="block text-sm font-medium text-gray-700">
        Destino
      </label>
      <select
        id="destination_id"
        v-model="formData.destination_id"
        required
        class="input mt-1"
        :class="{ 'border-red-500': errors.destination_id }"
      >
        <option value="">Selecione um destino</option>
        <option
          v-for="destination in destinations"
          :key="destination.value"
          :value="destination.value"
        >
          {{ destination.label }}
        </option>
      </select>
      <p v-if="errors.destination_id" class="mt-1 text-sm text-red-600">
        {{ errors.destination_id }}
      </p>
    </div>

    <div :class="isAdmin ? 'col-span-2' : 'col-span-3'">
      <label for="description" class="block text-sm font-medium text-gray-700">
        Descrição (opcional)
      </label>
      <input
        id="description"
        v-model="formData.description"
        type="text"
        class="input mt-1"
        placeholder="Motivo da viagem"
      />
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 col-span-3">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Data de Partida
        </label>
        <VueDatePicker
          v-model="formData.departure_datetime"
          :min-date="minDate"
          :enable-time-picker="true"
          :format="dateTimeFormat"
          :preview-format="dateTimeFormat"
          :format-locale="datePickerLocale"
          placeholder="Selecione data e hora"
          :class="{ 'dp-error': errors.departure_datetime }"
        />
        <p v-if="errors.departure_datetime" class="mt-1 text-sm text-red-600">
          {{ errors.departure_datetime }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Data de Retorno
        </label>
        <VueDatePicker
          v-model="formData.return_datetime"
          :min-date="formData.departure_datetime || minDate"
          :enable-time-picker="true"
          :format="dateTimeFormat"
          :preview-format="dateTimeFormat"
          :format-locale="datePickerLocale"
          placeholder="Selecione data e hora"
          :class="{ 'dp-error': errors.return_datetime }"
        />
        <p v-if="errors.return_datetime" class="mt-1 text-sm text-red-600">
          {{ errors.return_datetime }}
        </p>
      </div>
    </div>

    <div class="self-end col-span-1">
      <button
        v-if="showCancel"
        type="button"
        @click="$emit('cancel')"
        class="btn btn-secondary mr-2"
      >
        Cancelar
      </button>
      <button type="submit" :disabled="loading" class="btn btn-primary">
        {{ loading ? "Enviando..." : submitLabel }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { addHours } from "date-fns";
import { ptBR } from "date-fns/locale";
import { VueDatePicker } from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { useDestinationStore } from "@/stores/destination";
import { useTravelerStore } from "@/stores/traveler";

const datePickerLocale = ptBR;

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({}),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  submitLabel: {
    type: String,
    default: "Enviar",
  },
  showCancel: {
    type: Boolean,
    default: true,
  },
  isAdmin: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["submit", "cancel"]);

const destinationStore = useDestinationStore();
const travelerStore = useTravelerStore();

const formData = ref({
  traveler_id: props.initialData.traveler_id || "",
  destination_id: props.initialData.destination_id || "",
  description: props.initialData.description || "",
  departure_datetime: props.initialData.departure_datetime || null,
  return_datetime: props.initialData.return_datetime || null,
});

const errors = ref({});

const destinations = computed(() => destinationStore.destinationOptions);
const travelers = computed(() => travelerStore.travelerOptions);

const minDate = computed(() => addHours(new Date(), 1));

const dateTimeFormat = (date) => {
  if (!date) return "";
  const d = new Date(date);
  const day = String(d.getDate()).padStart(2, "0");
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const year = d.getFullYear();
  const hours = String(d.getHours()).padStart(2, "0");
  const minutes = String(d.getMinutes()).padStart(2, "0");
  return `${day}/${month}/${year} ${hours}:${minutes}`;
};

onMounted(async () => {
  if (destinationStore.destinations.length === 0) {
    await destinationStore.fetchAllDestinations();
  }
  if (props.isAdmin && travelerStore.travelers.length === 0) {
    await travelerStore.fetchTravelers(1, { is_active: true });
  }
});

watch(
  () => props.initialData,
  (newData) => {
    if (newData) {
      formData.value = {
        traveler_id: newData.traveler_id || "",
        destination_id: newData.destination_id || "",
        description: newData.description || "",
        departure_datetime: newData.departure_datetime || null,
        return_datetime: newData.return_datetime || null,
      };
    }
  },
  { deep: true }
);

watch(
  () => props.isAdmin,
  async (isAdmin) => {
    if (isAdmin && travelerStore.travelers.length === 0) {
      await travelerStore.fetchTravelers(1, { is_active: true });
    }
  }
);

function validateForm() {
  errors.value = {};

  if (props.isAdmin && !formData.value.traveler_id) {
    errors.value.traveler_id = "Selecione um viajante";
  }

  if (!formData.value.destination_id) {
    errors.value.destination_id = "Selecione um destino";
  }

  if (!formData.value.departure_datetime) {
    errors.value.departure_datetime = "Data de partida é obrigatória";
  }

  if (!formData.value.return_datetime) {
    errors.value.return_datetime = "Data de retorno é obrigatória";
  }

  if (formData.value.departure_datetime && formData.value.return_datetime) {
    if (
      new Date(formData.value.return_datetime) <=
      new Date(formData.value.departure_datetime)
    ) {
      errors.value.return_datetime = "Data de retorno deve ser após a partida";
    }
  }

  return Object.keys(errors.value).length === 0;
}

function formatDateForApi(date) {
  if (!date) return null;
  const d = new Date(date);
  return d.toISOString();
}

function handleSubmit() {
  if (validateForm()) {
    const data = {
      ...formData.value,
      departure_datetime: formatDateForApi(formData.value.departure_datetime),
      return_datetime: formatDateForApi(formData.value.return_datetime),
    };
    // Remove traveler_id if not admin (backend will use current user's traveler)
    if (!props.isAdmin) {
      delete data.traveler_id;
    }
    emit("submit", data);
  }
}

function resetForm() {
  formData.value = {
    traveler_id: "",
    destination_id: "",
    description: "",
    departure_datetime: null,
    return_datetime: null,
  };
  errors.value = {};
}

defineExpose({ resetForm });
</script>

<style scoped>
.dp-error :deep(.dp__input) {
  border-color: #ef4444;
}

:deep(.dp__input) {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
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
