<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ContractService from '@/services/ContractService'

const router = useRouter()
const generations = ref([])
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

function truncate(text, max) {
  if (!text) return ''
  return text.length > max ? text.slice(0, max) + '...' : text
}

function getServiceType(requirements) {
  if (!requirements) return 'Contrato'
  return requirements.service_type || 'Contrato'
}

onMounted(async () => {
  try {
    const { data } = await ContractService.getGenerations()
    generations.value = data.data
  } catch {
    error.value = 'No se pudieron cargar los contratos.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="generations">
    <div class="generations-header">
      <button class="generations-back" @click="router.push('/freelancer/home')">
        <i class="pi pi-arrow-left"></i>
        <span>Inicio</span>
      </button>
    </div>

    <div class="generations-title-row">
      <h1 class="generations-title">Contratos generados</h1>
      <button class="generations-new-btn" @click="router.push('/freelancer/generate')">
        <i class="pi pi-plus"></i>
        <span>Nuevo</span>
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="generations-empty">
      <i class="pi pi-spin pi-spinner"></i>
      <p>Cargando contratos...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="generations-empty">
      <p>{{ error }}</p>
    </div>

    <!-- Empty -->
    <div v-else-if="!generations.length" class="generations-empty">
      <i class="pi pi-file-plus"></i>
      <p>No has generado ningún contrato aún.</p>
      <button class="generations-start-btn" @click="router.push('/freelancer/generate')">
        Crear mi primer contrato
      </button>
    </div>

    <!-- List -->
    <div v-else class="generations-list">
      <button
        v-for="g in generations"
        :key="g.id"
        class="generation-card"
        @click="router.push(`/freelancer/generations/${g.id}`)"
      >
        <div class="generation-card-icon">
          <i class="pi pi-file"></i>
        </div>
        <div class="generation-card-body">
          <p class="generation-card-name">{{ getServiceType(g.requirements) }}</p>
          <p class="generation-card-summary">{{ truncate(g.ai_summary, 120) }}</p>
          <p class="generation-card-date">{{ formatDate(g.created_at) }}</p>
        </div>
        <i class="pi pi-chevron-right generation-card-arrow"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.generations {
  max-width: 680px;
  margin: 0 auto;
}

.generations-header {
  margin-bottom: 1rem;
}

.generations-back {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .5rem .75rem;
  border-radius: .5rem;
  color: var(--body);
  font-size: .875rem;
  transition: all .15s;
}

.generations-back:hover {
  background: var(--hover);
  color: var(--heading);
}

.generations-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.generations-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--heading);
}

.generations-new-btn {
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

.generations-new-btn:hover {
  background: var(--accent-hover);
}

.generations-empty {
  text-align: center;
  padding: 4rem 1rem;
  color: var(--body);
}

.generations-empty i {
  font-size: 2.5rem;
  display: block;
  margin-bottom: .75rem;
  color: var(--border);
}

.generations-empty p {
  font-size: .9375rem;
  margin-bottom: 1rem;
}

.generations-start-btn {
  display: inline-block;
  padding: .625rem 1.5rem;
  background: var(--accent);
  color: var(--accent-text);
  font-size: .9375rem;
  font-weight: 600;
  border-radius: .75rem;
  transition: background .15s;
}

.generations-start-btn:hover {
  background: var(--accent-hover);
}

.generations-list {
  display: flex;
  flex-direction: column;
  gap: .5rem;
}

.generation-card {
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

.generation-card:hover {
  border-color: var(--accent);
}

.generation-card-icon {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: .75rem;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.generation-card-icon i {
  font-size: 1.375rem;
  color: var(--accent);
}

.generation-card-body {
  flex: 1;
  min-width: 0;
}

.generation-card-name {
  font-size: .9375rem;
  font-weight: 600;
  color: var(--heading);
  margin-bottom: .25rem;
}

.generation-card-summary {
  font-size: .8125rem;
  color: var(--body);
  line-height: 1.5;
  margin-bottom: .375rem;
}

.generation-card-date {
  font-size: .75rem;
  color: var(--body);
}

.generation-card-arrow {
  font-size: .875rem;
  color: var(--body);
  flex-shrink: 0;
}
</style>
