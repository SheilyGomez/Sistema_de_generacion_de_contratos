<script setup>
import { ref, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import ContractService from '@/services/ContractService'

const router = useRouter()

const file = ref(null)
const dragging = ref(false)
const uploading = ref(false)
const error = ref('')
const inputRef = ref(null)

function onDragOver(e) {
  e.preventDefault()
  dragging.value = true
}

function onDragLeave() {
  dragging.value = false
}

function onDrop(e) {
  e.preventDefault()
  dragging.value = false
  const dropped = e.dataTransfer.files[0]
  if (dropped) setFile(dropped)
}

function onFilePicked(e) {
  const picked = e.target.files[0]
  if (picked) setFile(picked)
}

function setFile(f) {
  if (f.type !== 'application/pdf') {
    error.value = 'Solo se permiten archivos PDF.'
    file.value = null
    return
  }
  if (f.size > 10 * 1024 * 1024) {
    error.value = 'El archivo no debe superar los 10 MB.'
    file.value = null
    return
  }
  error.value = ''
  file.value = f
}

function formatSize(bytes) {
  if (!bytes) return '0 B'
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}

function clearFile() {
  file.value = null
  if (inputRef.value) inputRef.value.value = ''
}

async function upload() {
  if (!file.value) return

  uploading.value = true
  error.value = ''

  try {
    const formData = new FormData()
    formData.append('contract', file.value)
    const response = await ContractService.uploadVerification(formData)
    const id = response?.data?.data?.id
    if (id) {
      router.push(`/freelancer/verifications/${id}`)
    } else {
      router.push('/freelancer/verifications')
    }
  } catch (err) {
    if (err.response?.status === 422) {
      error.value = err.response.data.message || 'El archivo no es válido.'
    } else if (err.response?.status === 500) {
      error.value = 'Error del servidor. Intenta de nuevo.'
    } else if (err.code === 'ERR_NETWORK') {
      error.value = 'No se pudo conectar con el servidor.'
    } else {
      error.value = 'Error al subir el archivo. Intenta de nuevo.'
    }
  } finally {
    uploading.value = false
  }
}
</script>

<template>
  <div class="verify">
    <div class="verify-header">
      <button class="verify-back" @click="router.push('/freelancer/home')">
        <i class="pi pi-arrow-left"></i>
        <span>Inicio</span>
      </button>
    </div>

    <h1 class="verify-title">Verificar contrato</h1>
    <p class="verify-subtitle">Sube un PDF y la IA lo analizará en busca de cláusulas desfavorables.</p>

    <!-- Dropzone -->
    <div
      v-if="!file"
      class="dropzone"
      :class="{ 'dropzone--drag': dragging }"
      @dragover="onDragOver"
      @dragleave="onDragLeave"
      @drop="onDrop"
      @click="inputRef?.click()"
    >
      <i class="pi pi-cloud-upload dropzone-icon"></i>
      <p class="dropzone-text">Arrastra tu PDF aquí</p>
      <p class="dropzone-hint">o haz clic para seleccionar</p>
      <input
        ref="inputRef"
        type="file"
        accept="application/pdf"
        class="dropzone-input"
        @change="onFilePicked"
      />
    </div>

    <!-- File selected -->
    <div v-else class="file-card">
      <div class="file-card-info">
        <div class="file-card-icon">
          <i class="pi pi-file-pdf"></i>
        </div>
        <div class="file-card-body">
          <p class="file-card-name">{{ file.name }}</p>
          <p class="file-card-size">{{ formatSize(file.size) }}</p>
        </div>
      </div>
      <button class="file-card-remove" @click="clearFile" :disabled="uploading">
        <i class="pi pi-times"></i>
      </button>
    </div>

    <!-- Error -->
    <p v-if="error" class="verify-error">{{ error }}</p>

    <!-- Upload button -->
    <button
      v-if="file"
      class="verify-btn"
      :disabled="uploading"
      @click="upload"
    >
      <i v-if="uploading" class="pi pi-spin pi-spinner"></i>
      <span v-else>Verificar con IA</span>
      <span v-if="uploading">Analizando...</span>
    </button>
  </div>
</template>

<style scoped>
.verify {
  max-width: 520px;
  margin: 0 auto;
}

.verify-header {
  margin-bottom: 1.5rem;
}

.verify-back {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .5rem .75rem;
  border-radius: .5rem;
  color: var(--body);
  font-size: .875rem;
  transition: all .15s;
}

.verify-back:hover {
  background: var(--hover);
  color: var(--heading);
}

.verify-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--heading);
  margin-bottom: .5rem;
}

.verify-subtitle {
  font-size: .9375rem;
  color: var(--body);
  margin-bottom: 2rem;
}

.dropzone {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1.5rem;
  border: 2px dashed var(--border);
  border-radius: 1rem;
  cursor: pointer;
  transition: all .15s;
}

.dropzone:hover,
.dropzone--drag {
  border-color: var(--accent);
  background: var(--hover);
}

.dropzone-icon {
  font-size: 2.5rem;
  color: var(--accent);
  margin-bottom: 1rem;
}

.dropzone-text {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--heading);
  margin-bottom: .25rem;
}

.dropzone-hint {
  font-size: .875rem;
  color: var(--body);
}

.dropzone-input {
  display: none;
}

.file-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: .75rem;
  margin-bottom: 1rem;
}

.file-card-info {
  display: flex;
  align-items: center;
  gap: .75rem;
  min-width: 0;
}

.file-card-icon {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: .75rem;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.file-card-icon i {
  font-size: 1.5rem;
  color: var(--accent);
}

.file-card-body {
  min-width: 0;
}

.file-card-name {
  font-size: .9375rem;
  font-weight: 600;
  color: var(--heading);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-card-size {
  font-size: .8125rem;
  color: var(--body);
}

.file-card-remove {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: .5rem;
  color: var(--body);
  flex-shrink: 0;
  transition: all .15s;
}

.file-card-remove:hover {
  background: var(--danger-bg);
  color: var(--danger);
}

.verify-error {
  font-size: .875rem;
  color: var(--danger);
  margin-bottom: 1rem;
}

.verify-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  width: 100%;
  padding: .875rem 1.5rem;
  background: var(--accent);
  color: var(--accent-text);
  font-size: 1rem;
  font-weight: 600;
  border-radius: .75rem;
  transition: background .15s;
}

.verify-btn:hover:not(:disabled) {
  background: var(--accent-hover);
}

.verify-btn:disabled {
  opacity: .7;
  cursor: not-allowed;
}
</style>
