<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ContractService from '@/services/ContractService'
import { marked } from 'marked'

const route = useRoute()
const router = useRouter()

const verification = ref(null)
const loading = ref(true)
const error = ref('')
const downloading = ref(false)

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-MX', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

async function downloadFile() {
  downloading.value = true
  try {
    const res = await ContractService.downloadVerification(verification.value.id)
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', verification.value.original_file_name)
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

onMounted(async () => {
  try {
    const { data } = await ContractService.getVerification(route.params.id)
    verification.value = data.data
  } catch {
    error.value = 'No se pudo cargar la verificación.'
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
    <template v-else-if="verification">
      <div class="detail-header">
        <button class="detail-back" @click="router.push('/freelancer/verifications')">
          <i class="pi pi-arrow-left"></i>
          <span>Verificaciones</span>
        </button>
      </div>

      <div class="detail-title-row">
        <div class="detail-title-icon">
          <i class="pi pi-file-pdf"></i>
        </div>
        <div class="detail-title-body">
          <h1 class="detail-title">{{ verification.original_file_name }}</h1>
          <p class="detail-date">{{ formatDate(verification.created_at) }}</p>
        </div>
      </div>

      <!-- AI Summary -->
      <div class="detail-summary">
        <div class="detail-summary-header">
          <i class="pi pi-shield"></i>
          <span>Resultado del análisis</span>
        </div>
        <div
          class="detail-summary-content"
          v-html="marked.parse(verification.ai_summary)"
        ></div>
      </div>

      <div class="detail-actions">
        <button
          class="detail-download-btn"
          :disabled="downloading"
          @click="downloadFile"
        >
          <i :class="downloading ? 'pi pi-spin pi-spinner' : 'pi pi-download'"></i>
          <span>{{ downloading ? 'Descargando...' : 'Descargar PDF original' }}</span>
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

.detail-summary-content :deep(p) {
  margin-bottom: 1rem;
}

.detail-summary-content :deep(ul),
.detail-summary-content :deep(ol) {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
}

.detail-summary-content :deep(li) {
  margin-bottom: .375rem;
}

.detail-summary-content :deep(strong) {
  font-weight: 600;
  color: var(--heading);
}

.detail-summary-content :deep(em) {
  font-style: italic;
}

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
