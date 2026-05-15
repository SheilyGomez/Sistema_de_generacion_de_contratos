<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ContractService from '@/services/ContractService'

const router = useRouter()
const verifications = ref([])
const loading = ref(true)
const error = ref('')

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-MX', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function truncate(text, max) {
  if (!text) return ''
  return text.length > max ? text.slice(0, max) + '...' : text
}

onMounted(async () => {
  try {
    const { data } = await ContractService.getVerifications()
    verifications.value = data.data
  } catch {
    error.value = 'No se pudieron cargar las verificaciones.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="verifications">
    <div class="verifications-header">
      <button class="verifications-back" @click="router.push('/freelancer/home')">
        <i class="pi pi-arrow-left"></i>
        <span>Inicio</span>
      </button>
    </div>

    <div class="verifications-title-row">
      <h1 class="verifications-title">Contratos verificados</h1>
      <button class="verifications-new-btn" @click="router.push('/freelancer/verify')">
        <i class="pi pi-plus"></i>
        <span>Nuevo</span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="verifications-empty">
      <i class="pi pi-spin pi-spinner"></i>
      <p>Cargando verificaciones...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="verifications-empty">
      <p>{{ error }}</p>
    </div>

    <!-- Empty -->
    <div v-else-if="!verifications.length" class="verifications-empty">
      <i class="pi pi-file-pdf"></i>
      <p>No has verificado ningún contrato aún.</p>
      <button class="verifications-start-btn" @click="router.push('/freelancer/verify')">
        Verificar mi primer contrato
      </button>
    </div>

    <!-- List -->
    <div v-else class="verifications-list">
      <button
        v-for="v in verifications"
        :key="v.id"
        class="verification-card"
        @click="router.push(`/freelancer/verifications/${v.id}`)"
      >
        <div class="verification-card-icon">
          <i class="pi pi-file"></i>
        </div>
        <div class="verification-card-body">
          <p class="verification-card-name">{{ v.original_file_name }}</p>
          <p class="verification-card-summary">{{ truncate(v.ai_summary, 120) }}</p>
          <p class="verification-card-date">{{ formatDate(v.created_at) }}</p>
        </div>
        <i class="pi pi-chevron-right verification-card-arrow"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.verifications {
  max-width: 680px;
  margin: 0 auto;
}

.verifications-header {
  margin-bottom: 1rem;
}

.verifications-back {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .5rem .75rem;
  border-radius: .5rem;
  color: var(--body);
  font-size: .875rem;
  transition: all .15s;
}

.verifications-back:hover {
  background: var(--hover);
  color: var(--heading);
}

.verifications-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.verifications-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--heading);
}

.verifications-new-btn {
  display: inline-flex;
  align-items: center;
  gap: .375rem;
  padding: .5rem 1rem;
  width: fit-content;
  background: var(--accent);
  color: var(--accent-text);
  font-size: .875rem;
  font-weight: 600;
  border-radius: .75rem;
  transition: background .15s;
}

.verifications-new-btn:hover {
  background: var(--accent-hover);
}

.verifications-empty {
  text-align: center;
  padding: 4rem 1rem;
  color: var(--body);
}

.verifications-empty i {
  font-size: 2.5rem;
  display: block;
  margin-bottom: .75rem;
  color: var(--border);
}

.verifications-empty p {
  font-size: .9375rem;
  margin-bottom: 1rem;
}

.verifications-start-btn {
  display: inline-block;
  padding: .625rem 1.5rem;
  background: var(--accent);
  color: var(--accent-text);
  font-size: .9375rem;
  font-weight: 600;
  border-radius: .75rem;
  transition: background .15s;
}

.verifications-start-btn:hover {
  background: var(--accent-hover);
}

.verifications-list {
  display: flex;
  flex-direction: column;
  gap: .5rem;
}

.verification-card {
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

.verification-card:hover {
  border-color: var(--accent);
}

.verification-card-icon {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: .75rem;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.verification-card-icon i {
  font-size: 1.375rem;
  color: var(--accent);
}

.verification-card-body {
  flex: 1;
  min-width: 0;
}

.verification-card-name {
  font-size: .9375rem;
  font-weight: 600;
  color: var(--heading);
  margin-bottom: .25rem;
}

.verification-card-summary {
  font-size: .8125rem;
  color: var(--body);
  line-height: 1.5;
  margin-bottom: .375rem;
}

.verification-card-date {
  font-size: .75rem;
  color: var(--body);
}

.verification-card-arrow {
  font-size: .875rem;
  color: var(--body);
  flex-shrink: 0;
}
</style>
