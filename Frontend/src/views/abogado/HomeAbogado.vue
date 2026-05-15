<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import LawyerService from '@/services/LawyerService'

const router = useRouter()
const requests = ref([])
const loading = ref(true)
const error = ref('')

function timeAgo(dateStr) {
  const now = new Date()
  const date = new Date(dateStr)
  const diff = now - date
  const mins = Math.floor(diff / 60000)
  const hrs = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (mins < 1) return 'Ahora mismo'
  if (mins < 60) return `Hace ${mins} min`
  if (hrs < 24) return `Hace ${hrs} h`
  if (days === 1) return 'Ayer'
  return `Hace ${days} días`
}

function getStatusBadge(status) {
  const map = {
    pending: { label: 'Pendiente', class: 'badge-pending' },
    completed: { label: 'Completado', class: 'badge-completed' },
    rejected: { label: 'Rechazado', class: 'badge-rejected' },
  }
  return map[status] || { label: status, class: '' }
}

function getContractSummary(requirements) {
  if (!requirements) return 'Sin datos'
  return requirements.service_type || requirements.client_name || 'Contrato'
}

onMounted(async () => {
  try {
    const { data } = await LawyerService.getLawyerRequests()
    requests.value = data.data
  } catch {
    error.value = 'No se pudieron cargar las peticiones.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="home-abogado">
    <div class="abogado-header">
      <h1 class="abogado-title">Peticiones de revisión</h1>
      <span class="abogado-count">{{ requests.length }} peticiones</span>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="abogado-empty">
      <i class="pi pi-spin pi-spinner"></i>
      <p>Cargando peticiones...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="abogado-empty">
      <p>{{ error }}</p>
    </div>

    <!-- Empty -->
    <div v-else-if="!requests.length" class="abogado-empty">
      <i class="pi pi-inbox"></i>
      <p>No hay peticiones pendientes.</p>
    </div>

    <!-- List -->
    <div v-else class="abogado-list">
      <button
        v-for="req in requests"
        :key="req.id"
        class="request-card"
        @click="router.push(`/abogado/requests/${req.id}`)"
      >
        <div class="request-avatar">
          {{ req.freelancer?.name?.charAt(0)?.toUpperCase() }}
        </div>
        <div class="request-body">
          <p class="request-name">{{ req.freelancer?.name || 'Freelancer' }}</p>
          <p class="request-contract">{{ getContractSummary(req.contract_generation?.requirements) }}</p>
          <p class="request-time">{{ timeAgo(req.created_at) }}</p>
        </div>
        <div class="request-meta">
          <span class="request-price">${{ Number(req.price).toFixed(2) }}</span>
          <span class="request-badge" :class="getStatusBadge(req.status).class">
            {{ getStatusBadge(req.status).label }}
          </span>
        </div>
      </button>
    </div>
  </div>
</template>

<style scoped>
.home-abogado {
  max-width: 800px;
  margin: 0 auto;
}

.abogado-header {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.abogado-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--heading);
}

.abogado-count {
  font-size: .875rem;
  color: var(--body);
}

.abogado-empty {
  text-align: center;
  padding: 4rem 1rem;
  color: var(--body);
}

.abogado-empty i {
  font-size: 2.5rem;
  display: block;
  margin-bottom: .75rem;
  color: var(--border);
}

.abogado-empty p {
  font-size: .9375rem;
}

.abogado-list {
  display: flex;
  flex-direction: column;
  gap: .75rem;
}

.request-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: .75rem;
  cursor: pointer;
  transition: all .15s;
  text-align: left;
}

.request-card:hover {
  border-color: var(--accent);
  box-shadow: 0 2px 12px rgba(255, 203, 116, .08);
}

.request-avatar {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: .75rem;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--accent);
  flex-shrink: 0;
}

.request-body {
  flex: 1;
  min-width: 0;
}

.request-name {
  font-size: .9375rem;
  font-weight: 600;
  color: var(--heading);
  margin-bottom: .25rem;
}

.request-contract {
  font-size: .8125rem;
  color: var(--body);
  margin-bottom: .25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.request-time {
  font-size: .75rem;
  color: var(--body);
}

.request-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: .375rem;
  flex-shrink: 0;
}

.request-price {
  font-size: .9375rem;
  font-weight: 700;
  color: var(--accent);
}

.request-badge {
  font-size: .75rem;
  font-weight: 600;
  padding: .25rem .625rem;
  border-radius: .375rem;
}

.badge-pending {
  background: #FDE68A;
  color: #92400E;
}

.badge-completed {
  background: #A7F3D0;
  color: #065F46;
}

.badge-rejected {
  background: #FECACA;
  color: #991B1B;
}
</style>
