<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import LawyerService from '@/services/LawyerService'
import ContractService from '@/services/ContractService'
import { marked } from 'marked'

const route = useRoute()
const router = useRouter()

const request = ref(null)
const loading = ref(true)
const downloading = ref(false)
const downloadingCorrected = ref(false)
const error = ref('')

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-MX', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function getStatusBadge(status) {
  const map = {
    pending: { label: 'Pendiente', class: 'badge-pending' },
    completed: { label: 'Completado', class: 'badge-completed' },
    rejected: { label: 'Rechazado', class: 'badge-rejected' },
  }
  return map[status] || { label: status, class: '' }
}

function formatRequirements(req) {
  if (!req) return []
  return [
    { label: 'Tipo de servicio', value: req.service_type },
    { label: 'Duración', value: req.duration },
    { label: 'Monto', value: `${req.payment_amount} ${req.payment_currency}` },
    { label: 'Cliente', value: req.client_name },
    { label: 'Freelancer', value: req.freelancer_name },
    { label: 'Fecha de inicio', value: req.start_date },
  ].filter((r) => r.value)
}

async function downloadOriginal() {
  downloading.value = true
  try {
    const res = await ContractService.downloadGeneration(request.value.contract_generation_id)
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', request.value.contract_generation.generated_file_name)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch {
    // ignored
  } finally {
    downloading.value = false
  }
}

async function downloadCorrected() {
  downloadingCorrected.value = true
  try {
    const res = await LawyerService.downloadCorrectedContract(request.value.id)
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', request.value.corrected_file_name || 'contrato_corregido.pdf')
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch {
    // ignored
  } finally {
    downloadingCorrected.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await LawyerService.getLawyerRequest(route.params.id)
    request.value = data.data
  } catch {
    error.value = 'No se pudo cargar la revisión.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="detail">
    <!-- Loading -->
    <div v-if="loading" class="detail-empty">
      <i class="pi pi-spin pi-spinner"></i>
      <p>Cargando...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="detail-empty">
      <p>{{ error }}</p>
    </div>

    <!-- Content -->
    <template v-else-if="request">
      <div class="detail-header">
        <button class="detail-back" @click="router.push('/freelancer/lawyer-reviews')">
          <i class="pi pi-arrow-left"></i>
          <span>Verificaciones con abogado</span>
        </button>
      </div>

      <!-- Lawyer info + status -->
      <div class="detail-title-row">
        <div class="detail-avatar">
          {{ request.lawyer?.name?.charAt(0)?.toUpperCase() }}
        </div>
        <div class="detail-title-body">
          <h1 class="detail-title">{{ request.lawyer?.name || 'Abogado' }}</h1>
          <p class="detail-subtitle">Revisión de contrato</p>
        </div>
        <div class="detail-status">
          <span class="review-badge" :class="getStatusBadge(request.status).class">
            {{ getStatusBadge(request.status).label }}
          </span>
          <span class="review-price">${{ Number(request.price).toFixed(2) }}</span>
        </div>
      </div>

      <!-- Requirements -->
      <div class="detail-card">
        <div class="detail-card-header">
          <i class="pi pi-list"></i>
          <span>Requerimientos del contrato</span>
        </div>
        <div class="detail-card-body">
          <div
            v-for="r in formatRequirements(request.contract_generation?.requirements)"
            :key="r.label"
            class="req-row"
          >
            <span class="req-label">{{ r.label }}</span>
            <span class="req-value">{{ r.value }}</span>
          </div>
          <div
            v-if="request.contract_generation?.requirements?.extra_details"
            class="req-row req-row--extra"
          >
            <span class="req-label">Detalles extra</span>
            <span class="req-value">{{ request.contract_generation.requirements.extra_details }}</span>
          </div>
        </div>
      </div>

      <!-- AI Summary of original contract -->
      <div v-if="request.contract_generation?.ai_summary" class="detail-card">
        <div class="detail-card-header">
          <i class="pi pi-file-plus"></i>
          <span>Resumen del contrato original</span>
        </div>
        <div
          class="detail-card-content"
          v-html="marked.parse(request.contract_generation.ai_summary)"
        ></div>
      </div>

      <!-- Freelancer comment -->
      <div v-if="request.freelancer_comment" class="detail-card">
        <div class="detail-card-header">
          <i class="pi pi-comment"></i>
          <span>Tu comentario</span>
        </div>
        <div class="detail-card-body">
          <p class="comment-text">{{ request.freelancer_comment }}</p>
        </div>
      </div>

      <!-- Lawyer comment -->
      <div v-if="request.lawyer_comment" class="detail-card">
        <div class="detail-card-header">
          <i class="pi pi-user-edit"></i>
          <span>Comentario del abogado</span>
        </div>
        <div class="detail-card-body">
          <p class="comment-text">{{ request.lawyer_comment }}</p>
        </div>
      </div>

      <!-- Download buttons -->
      <div class="detail-actions">
        <button
          v-if="request.contract_generation?.generated_file_name"
          class="detail-download-btn"
          :disabled="downloading"
          @click="downloadOriginal"
        >
          <i :class="downloading ? 'pi pi-spin pi-spinner' : 'pi pi-download'"></i>
          <span>{{ downloading ? 'Descargando...' : 'Descargar contrato original' }}</span>
        </button>
        <button
          class="detail-download-btn"
          :disabled="downloadingCorrected || !request.corrected_file_name"
          @click="downloadCorrected"
        >
          <i :class="downloadingCorrected ? 'pi pi-spin pi-spinner' : 'pi pi-download'"></i>
          <span>{{ downloadingCorrected ? 'Descargando...' : 'Descargar contrato corregido' }}</span>
        </button>
      </div>
    </template>
  </div>
</template>

<style scoped>
.detail {
  max-width: 760px;
  margin: 0 auto;
}

.detail-empty {
  text-align: center;
  padding: 4rem 1rem;
  color: var(--body);
}

.detail-empty i {
  font-size: 2rem;
  display: block;
  margin-bottom: .5rem;
  color: var(--border);
}

.detail-empty p {
  font-size: .9375rem;
}

.detail-header {
  margin-bottom: 1.5rem;
}

.detail-back {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .5rem .75rem;
  border-radius: .5rem;
  color: var(--body);
  font-size: .875rem;
  transition: all .15s;
}

.detail-back:hover {
  background: var(--hover);
  color: var(--heading);
}

.detail-title-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.detail-avatar {
  width: 3.25rem;
  height: 3.25rem;
  border-radius: 1rem;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--accent);
  flex-shrink: 0;
}

.detail-title-body {
  flex: 1;
  min-width: 0;
}

.detail-title {
  font-size: 1.375rem;
  font-weight: 700;
  color: var(--heading);
  margin-bottom: .125rem;
}

.detail-subtitle {
  font-size: .8125rem;
  color: var(--body);
}

.detail-status {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: .375rem;
  flex-shrink: 0;
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

.review-price {
  font-size: .9375rem;
  font-weight: 700;
  color: var(--accent);
}

/* Cards */
.detail-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1rem;
  margin-bottom: 1.25rem;
  overflow: hidden;
}

.detail-card-header {
  display: flex;
  align-items: center;
  gap: .5rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
  font-size: .9375rem;
  font-weight: 600;
  color: var(--heading);
}

.detail-card-header i {
  color: var(--accent);
}

.detail-card-body {
  padding: .75rem 1.25rem;
}

.detail-card-content {
  padding: 1.25rem;
  font-size: .9375rem;
  color: var(--body);
  line-height: 1.8;
}

.req-row {
  display: flex;
  padding: .625rem 0;
  border-bottom: 1px solid var(--border);
}

.req-row:last-child {
  border-bottom: none;
}

.req-row--extra {
  flex-direction: column;
  gap: .25rem;
}

.req-label {
  font-size: .8125rem;
  font-weight: 600;
  color: var(--heading);
  width: 140px;
  flex-shrink: 0;
}

.req-row--extra .req-label {
  width: auto;
}

.req-value {
  font-size: .875rem;
  color: var(--body);
  flex: 1;
}

.comment-text {
  font-size: .9375rem;
  color: var(--body);
  line-height: 1.7;
  white-space: pre-wrap;
}

/* Markdown inside card */
.detail-card-content :deep(h1),
.detail-card-content :deep(h2),
.detail-card-content :deep(h3),
.detail-card-content :deep(h4) {
  font-weight: 600;
  color: var(--heading);
  margin: 1.5rem 0 .75rem;
}

.detail-card-content :deep(h1) { font-size: 1.25rem; }
.detail-card-content :deep(h2) { font-size: 1.125rem; }
.detail-card-content :deep(h3) { font-size: 1rem; }

.detail-card-content :deep(p) { margin-bottom: 1rem; }

.detail-card-content :deep(ul),
.detail-card-content :deep(ol) {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
}

.detail-card-content :deep(li) { margin-bottom: .375rem; }

.detail-card-content :deep(strong) {
  font-weight: 600;
  color: var(--heading);
}

.detail-card-content :deep(em) { font-style: italic; }

.detail-card-content :deep(code) {
  background: var(--hover);
  padding: .125rem .375rem;
  border-radius: .25rem;
  font-size: .875rem;
}

.detail-card-content :deep(pre) {
  background: var(--hover);
  padding: 1rem;
  border-radius: .5rem;
  overflow-x: auto;
  margin-bottom: 1rem;
}

.detail-card-content :deep(pre code) {
  background: none;
  padding: 0;
}

.detail-card-content :deep(blockquote) {
  border-left: 3px solid var(--accent);
  padding-left: 1rem;
  margin: 1rem 0;
  color: var(--body);
  font-style: italic;
}

.detail-card-content :deep(hr) {
  border: none;
  border-top: 1px solid var(--border);
  margin: 1.5rem 0;
}

.detail-card-content :deep(a) {
  color: var(--accent);
  text-decoration: underline;
}

/* Actions */
.detail-actions {
  display: flex;
  gap: .75rem;
}

.detail-download-btn {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .75rem 1.5rem;
  background: var(--accent);
  color: var(--accent-text);
  font-size: .9375rem;
  font-weight: 600;
  border-radius: .75rem;
  transition: all .15s;
}

.detail-download-btn:hover:not(:disabled) {
  background: var(--accent-hover);
}

.detail-download-btn:disabled {
  opacity: .7;
  cursor: not-allowed;
}
</style>
