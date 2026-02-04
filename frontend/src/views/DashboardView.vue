<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <DashboardHeader
      title="Trip Requests"
      :stats="periodStatsLabels"
      :user="authStore.user"
      :is-admin="authStore.isAdmin"
      :search-loading="searchLoading"
      @logout="handleLogout"
      @search="handleSearchById"
    />

    <!-- Search Result -->
    <div v-if="searchResult" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-lg font-semibold text-blue-900 mb-2">
              Resultado da Pesquisa - ID #{{ searchResult.id }}
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div>
                <span class="text-gray-600">Viajante:</span>
                <p class="font-medium">{{ searchResult.traveler?.name || 'N/A' }}</p>
              </div>
              <div>
                <span class="text-gray-600">Destino:</span>
                <p class="font-medium">{{ searchResult.destination?.name || 'N/A' }}</p>
              </div>
              <div>
                <span class="text-gray-600">Status:</span>
                <span
                  :class="[
                    'inline-block px-2 py-1 rounded-full text-xs font-medium',
                    searchResult.status === 'approved' ? 'bg-green-100 text-green-800' :
                    searchResult.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                    'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ statusLabel(searchResult.status) }}
                </span>
              </div>
              <div>
                <span class="text-gray-600">Partida:</span>
                <p class="font-medium">{{ formatDate(searchResult.departure_datetime) }}</p>
              </div>
            </div>
          </div>
          <button
            @click="clearSearchResult"
            class="text-blue-600 hover:text-blue-800"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Search Error -->
    <div v-if="searchError" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
      <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex justify-between items-center">
        <p class="text-red-800">{{ searchError }}</p>
        <button
          @click="searchError = null"
          class="text-red-600 hover:text-red-800"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Create Trip Form -->
      <div class="mb-8">
        <CreateTripCard
          ref="createTripCardRef"
          :is-admin="authStore.isAdmin"
          @submit="handleCreateTrip"
          @success="loadTripRequests"
        />
      </div>

      <!-- Content with sidebar filters -->
      <div class="flex gap-6">
        <!-- Filters Sidebar -->
        <div class="w-64 flex-shrink-0">
          <TripFilters
            v-model="filters"
            :has-active-filters="tripStore.hasFilters"
            @update:model-value="debounceFilter"
            @clear="clearFilters"
          />
        </div>

        <!-- Data Table with Tabs -->
        <div class="flex-1 min-w-0">
          <!-- Status Tabs -->
          <div class="bg-white rounded-t-lg border border-b-0 border-gray-200">
            <nav class="flex" aria-label="Tabs">
              <button
                v-for="tab in statusTabs"
                :key="tab.value"
                @click="setActiveTab(tab.value)"
                :class="[
                  'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                  activeTab === tab.value
                    ? 'border-blue-500 text-blue-600 bg-blue-50'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                {{ tab.label }}
                <span
                  v-if="tab.count !== undefined"
                  :class="[
                    'ml-2 px-2 py-0.5 rounded-full text-xs',
                    activeTab === tab.value
                      ? 'bg-blue-100 text-blue-600'
                      : 'bg-gray-100 text-gray-600'
                  ]"
                >
                  {{ tab.count }}
                </span>
              </button>
            </nav>
          </div>

          <!-- Table -->
          <TripTable
            :data="filteredTripRequests"
            :loading="tripStore.loading"
            :pagination="tripStore.pagination"
            :is-admin="authStore.isAdmin"
            @page-change="handlePageChange"
            @cancel="handleDelete"
            @approve="(id) => updateStatus(id, 'approved')"
            @reject="(id) => updateStatus(id, 'cancelled')"
          />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useTripStore } from "@/stores/trip";
import {
  startOfWeek,
  endOfWeek,
  startOfMonth,
  endOfMonth,
  isToday,
  isWithinInterval,
  parseISO,
} from "date-fns";
import {
  DashboardHeader,
  CreateTripCard,
  TripFilters,
  TripTable,
} from "@/components/dashboard";

const router = useRouter();
const authStore = useAuthStore();
const tripStore = useTripStore();

const activeTab = ref("");

const statusTabs = computed(() => [
  { label: "Todas", value: "", count: statusCounts.value.all },
  { label: "Solicitado", value: "requested", count: statusCounts.value.requested },
  { label: "Aprovado", value: "approved", count: statusCounts.value.approved },
  { label: "Cancelado", value: "cancelled", count: statusCounts.value.cancelled },
]);

const statusCounts = computed(() => {
  const trips = tripStore.tripRequests;
  return {
    all: trips.length,
    requested: trips.filter((t) => t.status === "requested").length,
    approved: trips.filter((t) => t.status === "approved").length,
    cancelled: trips.filter((t) => t.status === "cancelled").length,
  };
});

// Filtra localmente pelo status da tab selecionada
const filteredTripRequests = computed(() => {
  const trips = tripStore.tripRequests;
  if (!activeTab.value) return trips;
  return trips.filter((t) => t.status === activeTab.value);
});

const periodStatsLabels = computed(() => [
  { label: "Hoje", value: periodStats.value.today },
  { label: "Semana", value: periodStats.value.week },
  { label: "Mês", value: periodStats.value.month },
]);

const filters = ref({
  status: [],
  user: "",
  destination: "",
  start_date: "",
  end_date: "",
});

const createTripCardRef = ref(null)
const searchResult = ref(null)
const searchError = ref(null)
const searchLoading = ref(false);

const today = new Date();
const weekStart = computed(() => startOfWeek(today, { weekStartsOn: 0 }));
const weekEnd = computed(() => endOfWeek(today, { weekStartsOn: 0 }));
const monthStart = computed(() => startOfMonth(today));
const monthEnd = computed(() => endOfMonth(today));

const periodStats = computed(() => {
  const trips = tripStore.tripRequests;

  const todayCount = trips.filter((trip) => {
    if (!trip.departure_datetime) return false;
    const departureDate = parseISO(trip.departure_datetime);
    return isToday(departureDate);
  }).length;

  const weekCount = trips.filter((trip) => {
    if (!trip.departure_datetime) return false;
    const departureDate = parseISO(trip.departure_datetime);
    return isWithinInterval(departureDate, {
      start: weekStart.value,
      end: weekEnd.value,
    });
  }).length;

  const monthCount = trips.filter((trip) => {
    if (!trip.departure_datetime) return false;
    const departureDate = parseISO(trip.departure_datetime);
    return isWithinInterval(departureDate, {
      start: monthStart.value,
      end: monthEnd.value,
    });
  }).length;

  return {
    today: todayCount,
    week: weekCount,
    month: monthCount,
  };
});

let debounceTimeout = null;

onMounted(() => {
  loadTripRequests();
});

function setActiveTab(status) {
  activeTab.value = status;
  // Filtragem local - não precisa fazer requisição
}

async function loadTripRequests() {
  await tripStore.fetchTripRequests();
}

function applyFilters() {
  tripStore.setFilters(filters.value);
  loadTripRequests();
}

function debounceFilter() {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
}

function clearFilters() {
  activeTab.value = "";
  filters.value = {
    status: [],
    user: "",
    destination: "",
    start_date: "",
    end_date: "",
  };
  tripStore.clearFilters();
  loadTripRequests();
}

async function handleSearchById(id) {
  if (!id) {
    clearSearchResult();
    return;
  }

  searchError.value = null;
  searchResult.value = null;
  searchLoading.value = true;

  try {
    const result = await tripStore.fetchTripRequest(id);
    searchResult.value = result;
  } catch (error) {
    searchError.value = `Viagem com ID #${id} não encontrada.`;
  } finally {
    searchLoading.value = false;
  }
}

function clearSearchResult() {
  searchResult.value = null;
  searchError.value = null;
}

function statusLabel(status) {
  const labels = {
    requested: 'Solicitado',
    approved: 'Aprovado',
    cancelled: 'Cancelado'
  };
  return labels[status] || status;
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = parseISO(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}`;
}

function handlePageChange(page) {
  tripStore.fetchTripRequests(page);
}

async function handleCreateTrip(formData) {
  await tripStore.createTripRequest(formData);
  loadTripRequests();
}

async function handleDelete(id) {
  if (confirm("Tem certeza que deseja cancelar esta solicitação de viagem?")) {
    try {
      await tripStore.deleteTripRequest(id);
    } catch (error) {
      alert("Falha ao cancelar solicitação de viagem");
    }
  }
}

async function updateStatus(id, status) {
  try {
    await tripStore.updateTripRequestStatus(id, status);
  } catch (error) {
    alert("Falha ao atualizar status");
  }
}

async function handleLogout() {
  await authStore.logout();
  router.push({ name: "login" });
}
</script>
