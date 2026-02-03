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
          :initial-data="{ requester_name: authStore.user?.name }"
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

        <!-- Data Table -->
        <div class="flex-1 min-w-0">
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
    const departureDate = parseISO(trip.departure_date);
    return isToday(departureDate);
  }).length;

  const weekCount = trips.filter((trip) => {
    const departureDate = parseISO(trip.departure_date);
    return isWithinInterval(departureDate, {
      start: weekStart.value,
      end: weekEnd.value,
    });
  }).length;

  const monthCount = trips.filter((trip) => {
    const departureDate = parseISO(trip.departure_date);
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
  if (confirm("Are you sure you want to cancel this trip request?")) {
    try {
      await tripStore.deleteTripRequest(id);
    } catch (error) {
      alert("Failed to cancel trip request");
    }
  }
}

async function updateStatus(id, status) {
  try {
    await tripStore.updateTripRequestStatus(id, status);
  } catch (error) {
    alert("Failed to update status");
  }
}

async function handleLogout() {
  await authStore.logout();
  router.push({ name: "login" });
}
</script>
