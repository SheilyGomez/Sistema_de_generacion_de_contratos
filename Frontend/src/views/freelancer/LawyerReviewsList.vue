<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ContractService from '@/services/ContractService'

const router = useRouter()
const requests = ref([])
const loading = ref(true)
const error = ref('')

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-MX', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function getServiceType(req) {
  if (!req?.contract_generation?.requirements) return 'Contrato'
  return req.contract_generation.requirements.service_type || 'Contrato'
}

function getStatusBadge(status) {
  const map = {
    pending: { label: 'Pendiente', class: 'badge-pending' },
    completed: { label: 'Completado', class: 'badge-completed' },
    rejected: { label: 'Rechazado', class: 'badge-rejected' },
  }
  return map[status] || { label: status, class: '' }
}

onMounted(async () => {
  try {
    const { data } = await ContractService.getMyLawyerRequests()
    requests.value = data.data
  } catch {
    error.value = 'No se pudieron cargar las revisiones.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="reviews">
    <div class="reviews-header">
      <button class="reviews-back" @click="router.push('/freelancer/home')">
        <i class="pi pi-arrow-left"></i>
        <span>Inicio</span>
      </button>
    </div>

    <h1 class="reviews-title">Verificaciones con abogado</h1>

    <!-- Loading -->
    <div v-if="loading" class="reviews-empty">
      <i class="pi pi-spin pi-spinner"></i>
      <p>Cargando revisiones...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="reviews-empty">
      <p>{{ error }}</p>
    </div>

    <!-- Empty -->
    <div v-else-if="!requests.length" class="reviews-empty">
      <i class="pi pi-inbox"></i>
      <p>No has solicitado ninguna revisión con abogado.</p>
    </div>

    <!-- List -->
    <div v-else class="reviews-list">
      <button
        v-for="r in requests"
        :key="r.id"
        class="review-card"
        @click="router.push(`/freelancer/lawyer-reviews/${r.id}`)"
      >
        <div class="review-avatar">
          {{ r.lawyer?.name?.charAt(0)?.toUpperCase() }}
        </div>
        <div class="review-body">
          <p class="review-name">{{ r.lawyer?.name || 'Abogado' }}</p>
          <p class="review-contract">{{ getServiceType(r) }}</p>
          <p class="review-date">{{ formatDate(r.created_at) }}</p>
        </div>
        <div class="review-meta">
          <span class="review-price">${{ Number(r.price).toFixed(2) }}</span>
          <span class="review-badge" :class="getStatusBadge(r.status).class">
            {{ getStatusBadge(r.status).label }}
          </span>
        </div>
      </button>
    </div>
  </div>
</template>

<style scoped>
.reviews {
  max-width: 680px;
  margin: 0 auto;
}

.reviews-header {
  margin-bottom: 1rem;
}

.reviews-back {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .5rem .75rem;
  border-radius: .5rem;
  color: var(--body);
  font-size: .875rem;
  transition: all .15s;
}

.reviews-back:hover {
  background: var(--hover);
  color: var(--heading);
}

.reviews-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--heading);
  margin-bottom: 1.5rem;
}

.reviews-empty {
  text-align: center;
  padding: 4rem 1rem;
  color: var(--body);
}

.reviews-empty i {
  font-size: 2.5rem;
  display: block;
  margin-bottom: .75rem;
  color: var(--border);
}

.reviews-empty p {
  font-size: .9375rem;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: .5rem;
}

.review-card {
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

.review-card:hover {
  border-color: var(--accent);
}

.review-avatar {
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

.review-body {
  flex: 1;
  min-width: 0;
}

.review-name {
  font-size: .9375rem;
  font-weight: 600;
  color: var(--heading);
  margin-bottom: .25rem;
}

.review-contract {
  font-size: .8125rem;
  color: var(--body);
  margin-bottom: .25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.review-date {
  font-size: .75rem;
  color: var(--body);
}

.review-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: .375rem;
  flex-shrink: 0;
}

.review-price {
  font-size: .9375rem;
  font-weight: 700;
  color: var(--accent);
}

.review-badge {
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
