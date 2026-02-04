<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <DashboardHeader
      title="Trip Requests"
      :stats="periodStatsLabels"
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
            :data="tripStore.tripRequests"
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

const createTripCardRef = ref(null);

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
  authStore.initializeAuth();
  loadTripRequests();
});

function setActiveTab(status) {
  activeTab.value = status;
  filters.value.status = status ? [status] : [];
  applyFilters();
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
