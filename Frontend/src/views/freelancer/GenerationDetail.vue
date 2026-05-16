<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ContractService from '@/services/ContractService'
import LawyerService from '@/services/LawyerService'
import { useWalletStore } from '@/stores/wallet'
import { useAuthStore } from '@/stores/auth'
import { marked } from 'marked'

const route = useRoute()
const router = useRouter()
const wallet = useWalletStore()
const auth = useAuthStore()

const generation = ref(null)
const loading = ref(true)
const downloading = ref(false)
const error = ref('')

// Review modal
const showReviewModal = ref(false)
const reviewing = ref(false)
const reviewError = ref('')
const lawyers = ref([])
const reviewForm = ref({
  lawyer_id: '',
  freelancer_comment: 'Por favor revisa este contrato y verifica que no tenga cláusulas desfavorables.',
})
const REVIEW_PRICE = 50.0

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-MX', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
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

async function downloadFile() {
  downloading.value = true
  try {
    const res = await ContractService.downloadGeneration(generation.value.id)
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `contrato_${generation.value.id}.pdf`)
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

async function openReviewModal() {
  reviewError.value = ''
  reviewForm.value.freelancer_comment = 'Por favor revisa este contrato y verifica que no tenga cláusulas desfavorables.'
  try {
    const { data } = await LawyerService.getLawyers()
    lawyers.value = data.data || data
    if (!reviewForm.value.lawyer_id && lawyers.value.length) {
      reviewForm.value.lawyer_id = lawyers.value[0].id
    }
  } catch {
    lawyers.value = []
  }
  showReviewModal.value = true
}

async function submitReview() {
  if (!reviewForm.value.lawyer_id) return
  reviewing.value = true
  reviewError.value = ''

  try {
    await ContractService.requestReview(generation.value.id, reviewForm.value)
    showReviewModal.value = false
    wallet.fetchBalance()
  } catch (err) {
    if (err.response?.status === 422) {
      reviewError.value = err.response.data.message || 'Datos inválidos.'
    } else if (err.response?.status === 400) {
      reviewError.value = err.response.data.message || 'Saldo insuficiente.'
    } else {
      reviewError.value = 'Error al enviar la solicitud.'
    }
  } finally {
    reviewing.value = false
  }
}

const canRequestReview = () => {
  return wallet.balance >= REVIEW_PRICE
}

onMounted(async () => {
  try {
    const { data } = await ContractService.getGeneration(route.params.id)
    generation.value = data.data
    if (auth.isFreelancer) {
      wallet.fetchBalance()
    }
  } catch {
    error.value = 'No se pudo cargar el contrato.'
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
    <template v-else-if="generation">
      <div class="detail-header">
        <button class="detail-back" @click="router.push('/freelancer/generations')">
          <i class="pi pi-arrow-left"></i>
          <span>Contratos generados</span>
        </button>
      </div>

      <div class="detail-title-row">
        <div class="detail-title-icon">
          <i class="pi pi-file-pdf"></i>
        </div>
        <div class="detail-title-body">
          <h1 class="detail-title">{{ generation.requirements?.service_type || 'Contrato' }}</h1>
          <p class="detail-date">{{ formatDate(generation.created_at) }}</p>
        </div>
      </div>

      <!-- AI Summary -->
      <div v-if="generation.ai_summary" class="detail-summary">
        <div class="detail-summary-header">
          <i class="pi pi-file-plus"></i>
          <span>Resumen del contrato</span>
        </div>
        <div class="detail-summary-content" v-html="marked.parse(generation.ai_summary)"></div>
      </div>

      <!-- Requirements -->
      <div class="detail-requirements">
        <div class="detail-requirements-header">
          <i class="pi pi-list"></i>
          <span>Requerimientos ingresados</span>
        </div>
        <div class="detail-requirements-list">
          <div
            v-for="r in formatRequirements(generation.requirements)"
            :key="r.label"
            class="req-row"
          >
            <span class="req-label">{{ r.label }}</span>
            <span class="req-value">{{ r.value }}</span>
          </div>
          <div v-if="generation.requirements?.extra_details" class="req-row req-row--extra">
            <span class="req-label">Detalles extra</span>
            <span class="req-value">{{ generation.requirements.extra_details }}</span>
          </div>
        </div>
      </div>

      <div class="detail-actions">
        <button
          class="detail-download-btn"
          :disabled="downloading"
          @click="downloadFile"
        >
          <i :class="downloading ? 'pi pi-spin pi-spinner' : 'pi pi-download'"></i>
          <span>{{ downloading ? 'Descargando...' : 'Descargar PDF' }}</span>
        </button>

        <button
          class="detail-review-btn"
          :disabled="!canRequestReview()"
          @click="openReviewModal"
        >
          <i class="pi pi-user-edit"></i>
          <span>Solicitar revisión con abogado — ${{ REVIEW_PRICE.toFixed(2) }}</span>
        </button>
        <p v-if="!canRequestReview()" class="review-hint">
          Saldo insuficiente. Necesitas al menos ${{ REVIEW_PRICE.toFixed(2) }} para solicitar una revisión.
        </p>
      </div>
    </template>

    <!-- Review Modal -->
    <Teleport to="body">
      <div v-if="showReviewModal" class="modal-overlay" @click.self="showReviewModal = false">
        <div class="modal">
          <h2 class="modal-title">Solicitar revisión con abogado</h2>

          <div class="form-group">
            <label>Abogado</label>
            <select v-model="reviewForm.lawyer_id">
              <option v-for="l in lawyers" :key="l.id" :value="l.id">
                {{ l.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Comentario para el abogado</label>
            <textarea
              v-model="reviewForm.freelancer_comment"
              rows="3"
              maxlength="2000"
            ></textarea>
          </div>

          <p v-if="reviewError" class="modal-error">{{ reviewError }}</p>

          <div class="modal-actions">
            <button
              class="modal-cancel"
              @click="showReviewModal = false"
            >
              Cancelar
            </button>
            <button
              class="modal-confirm"
              :disabled="reviewing || !reviewForm.lawyer_id"
              @click="submitReview"
            >
              <i v-if="reviewing" class="pi pi-spin pi-spinner"></i>
              <span>{{ reviewing ? 'Enviando...' : 'Enviar solicitud' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
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
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 2rem;
}

.detail-title-icon {
  width: 3.25rem;
  height: 3.25rem;
  border-radius: 1rem;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.detail-title-icon i {
  font-size: 1.75rem;
  color: var(--accent);
}

.detail-title-body {
  min-width: 0;
}

.detail-title {
  font-size: 1.375rem;
  font-weight: 700;
  color: var(--heading);
  margin-bottom: .25rem;
  word-break: break-word;
}

.detail-date {
  font-size: .8125rem;
  color: var(--body);
}

/* AI Summary */
.detail-summary {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1rem;
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.detail-summary-header {
  display: flex;
  align-items: center;
  gap: .5rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
  font-size: .9375rem;
  font-weight: 600;
  color: var(--heading);
}

.detail-summary-header i {
  color: var(--accent);
}

.detail-summary-content {
  padding: 1.25rem;
  font-size: .9375rem;
  color: var(--body);
  line-height: 1.8;
}

.detail-summary-content :deep(h1),
.detail-summary-content :deep(h2),
.detail-summary-content :deep(h3),
.detail-summary-content :deep(h4) {
  font-weight: 600;
  color: var(--heading);
  margin: 1.5rem 0 .75rem;
}

.detail-summary-content :deep(h1) { font-size: 1.25rem; }
.detail-summary-content :deep(h2) { font-size: 1.125rem; }
.detail-summary-content :deep(h3) { font-size: 1rem; }

.detail-summary-content :deep(p) { margin-bottom: 1rem; }

.detail-summary-content :deep(ul),
.detail-summary-content :deep(ol) {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
}

.detail-summary-content :deep(li) { margin-bottom: .375rem; }

.detail-summary-content :deep(strong) {
  font-weight: 600;
  color: var(--heading);
}

.detail-summary-content :deep(em) { font-style: italic; }

.detail-summary-content :deep(code) {
  background: var(--hover);
  padding: .125rem .375rem;
  border-radius: .25rem;
  font-size: .875rem;
}

.detail-summary-content :deep(pre) {
  background: var(--hover);
  padding: 1rem;
  border-radius: .5rem;
  overflow-x: auto;
  margin-bottom: 1rem;
}

.detail-summary-content :deep(pre code) {
  background: none;
  padding: 0;
}

.detail-summary-content :deep(blockquote) {
  border-left: 3px solid var(--accent);
  padding-left: 1rem;
  margin: 1rem 0;
  color: var(--body);
  font-style: italic;
}

.detail-summary-content :deep(hr) {
  border: none;
  border-top: 1px solid var(--border);
  margin: 1.5rem 0;
}

.detail-summary-content :deep(a) {
  color: var(--accent);
  text-decoration: underline;
}

/* Requirements */
.detail-requirements {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1rem;
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.detail-requirements-header {
  display: flex;
  align-items: center;
  gap: .5rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
  font-size: .9375rem;
  font-weight: 600;
  color: var(--heading);
}

.detail-requirements-header i {
  color: var(--accent);
}

.detail-requirements-list {
  padding: .75rem 1.25rem;
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

/* Actions */
.detail-actions {
  display: flex;
  flex-direction: column;
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
  width: fit-content;
}

.detail-download-btn:hover:not(:disabled) {
  background: var(--accent-hover);
}

.detail-download-btn:disabled {
  opacity: .7;
  cursor: not-allowed;
}

.detail-review-btn {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .75rem 1.5rem;
  background: var(--surface);
  border: 1px solid var(--accent);
  color: var(--accent);
  font-size: .9375rem;
  font-weight: 600;
  border-radius: .75rem;
  transition: all .15s;
  width: fit-content;
}

.detail-review-btn:hover:not(:disabled) {
  background: rgba(255, 203, 116, .1);
}

.detail-review-btn:disabled {
  opacity: .4;
  cursor: not-allowed;
}

.review-hint {
  font-size: .8125rem;
  color: var(--body);
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, .5);
  padding: 1rem;
}

.modal {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 1.5rem;
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--heading);
  margin-bottom: 1.25rem;
}

.modal .form-group {
  margin-bottom: 1rem;
}

.modal label {
  display: block;
  font-size: .875rem;
  font-weight: 600;
  color: var(--heading);
  margin-bottom: .375rem;
}

.modal select,
.modal textarea {
  width: 100%;
  padding: .625rem .75rem;
  border: 1px solid var(--border);
  border-radius: .5rem;
  background: var(--surface);
  color: var(--heading);
  font-size: .9375rem;
  outline: none;
}

.modal select:focus,
.modal textarea:focus {
  border-color: var(--accent);
}

.modal textarea {
  resize: vertical;
}

.modal-error {
  font-size: .8125rem;
  color: var(--danger);
  margin-bottom: .75rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: .75rem;
  margin-top: .5rem;
}

.modal-cancel {
  padding: .625rem 1.25rem;
  background: var(--hover);
  color: var(--body);
  font-size: .875rem;
  font-weight: 600;
  border-radius: .75rem;
  transition: all .15s;
}

.modal-cancel:hover {
  background: var(--border);
}

.modal-confirm {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .625rem 1.25rem;
  background: var(--accent);
  color: var(--accent-text);
  font-size: .875rem;
  font-weight: 600;
  border-radius: .75rem;
  transition: background .15s;
}

.modal-confirm:hover:not(:disabled) {
  background: var(--accent-hover);
}

.modal-confirm:disabled {
  opacity: .7;
  cursor: not-allowed;
}
</style>
