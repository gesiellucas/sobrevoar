<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <DashboardHeader
      title="Trip Requests"
      :user="authStore.user"
      :is-admin="authStore.isAdmin"
      @logout="handleLogout"
    />

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
          <!-- Status Tabs with Search -->
          <div class="bg-white rounded-t-lg border border-b-0 border-gray-200">
            <div class="flex items-center justify-between px-4 py-2">
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

              <!-- Search -->
              <div class="relative">
                <input
                  v-model="searchValue"
                  type="text"
                  placeholder="Buscar por ID..."
                  :disabled="tripStore.loading"
                  class="px-3 py-1.5 pl-9 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed w-40"
                  @keyup.enter="handleSearchEnter"
                />
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <button
                  v-if="searchValue && !tripStore.loading"
                  @click="clearSearch"
                  class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
                <!-- Loading spinner -->
                <div
                  v-if="tripStore.loading"
                  class="absolute right-2 top-1/2 -translate-y-1/2"
                >
                  <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </div>
            </div>
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

const filters = ref({
  status: [],
  user: "",
  destination: "",
  start_date: "",
  end_date: "",
});

const createTripCardRef = ref(null)
const searchValue = ref('')

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

async function handleSearchEnter() {
  if (searchValue.value.trim()) {
    const id = searchValue.value.trim()

    // Clear other filters and tabs
    activeTab.value = ''
    filters.value = {
      status: [],
      user: "",
      destination: "",
      start_date: "",
      end_date: "",
    }
    tripStore.clearFilters()

    try {
      // Fetch specific trip request by ID
      await tripStore.fetchTripRequest(id)

      // Replace tripRequests with just this one result
      if (tripStore.currentTripRequest) {
        tripStore.tripRequests = [tripStore.currentTripRequest]
        tripStore.pagination = {
          currentPage: 1,
          lastPage: 1,
          perPage: 1,
          total: 1,
        }
      }
    } catch (error) {
      // If not found, clear results and show message
      tripStore.tripRequests = []
      tripStore.pagination = {
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 0,
      }
      alert('Trip request não encontrado com ID: ' + id)
    }
  }
}

function clearSearch() {
  searchValue.value = ''
  tripStore.clearFilters()
  loadTripRequests()
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
