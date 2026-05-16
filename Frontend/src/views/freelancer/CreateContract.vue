<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import ContractService from '@/services/ContractService'

const router = useRouter()

const currencies = ['USD', 'MXN', 'PEN', 'EUR', 'COP', 'ARS', 'CLP', 'BRL']

const form = reactive({
  service_type: '',
  duration: '',
  payment_amount: '',
  payment_currency: 'USD',
  client_name: '',
  freelancer_name: '',
  start_date: '',
  extra_details: '',
})

const submitting = ref(false)
const error = ref('')
const fieldErrors = ref({})

function getFieldError(field) {
  return fieldErrors.value[field]?.[0] || ''
}

async function submit() {
  error.value = ''
  fieldErrors.value = {}

  if (
    !form.service_type ||
    !form.duration ||
    !form.payment_amount ||
    !form.payment_currency ||
    !form.client_name ||
    !form.freelancer_name ||
    !form.start_date
  ) {
    error.value = 'Todos los campos obligatorios deben completarse.'
    return
  }

  submitting.value = true

  try {
    const { data } = await ContractService.createGeneration({
      ...form,
      payment_amount: Number(form.payment_amount),
    })
    const id = data?.data?.id
    if (id) {
      router.push(`/freelancer/generations/${id}`)
    } else {
      router.push('/freelancer/generations')
    }
  } catch (err) {
    if (err.response?.status === 422) {
      fieldErrors.value = err.response.data.errors || {}
      const messages = Object.values(fieldErrors.value).flat().join(' ')
      if (messages) error.value = messages
    } else {
      error.value = 'Error al generar el contrato. Intenta de nuevo.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="generate">
    <div class="generate-header">
      <button class="generate-back" @click="router.push('/freelancer/home')">
        <i class="pi pi-arrow-left"></i>
        <span>Inicio</span>
      </button>
    </div>

    <h1 class="generate-title">Crear contrato nuevo</h1>
    <p class="generate-subtitle">Completa los datos y la IA generará el contrato.</p>

    <form @submit.prevent="submit" class="generate-form">
      <!-- service_type -->
      <div class="form-group">
        <label>Tipo de servicio <span class="required">*</span></label>
        <input
          v-model="form.service_type"
          type="text"
          placeholder="Ej: Desarrollo de software, Consultoría..."
          :class="{ 'input-error': getFieldError('service_type') }"
        />
        <p v-if="getFieldError('service_type')" class="field-error">{{ getFieldError('service_type') }}</p>
      </div>

      <!-- duration -->
      <div class="form-group">
        <label>Duración <span class="required">*</span></label>
        <input
          v-model="form.duration"
          type="text"
          placeholder="Ej: 6 meses, 1 año..."
          :class="{ 'input-error': getFieldError('duration') }"
        />
        <p v-if="getFieldError('duration')" class="field-error">{{ getFieldError('duration') }}</p>
      </div>

      <!-- amount + currency -->
      <div class="form-row">
        <div class="form-group form-group--amount">
          <label>Monto <span class="required">*</span></label>
          <input
            v-model="form.payment_amount"
            type="number"
            min="0"
            step="0.01"
            placeholder="5000"
            :class="{ 'input-error': getFieldError('payment_amount') }"
          />
          <p v-if="getFieldError('payment_amount')" class="field-error">{{ getFieldError('payment_amount') }}</p>
        </div>
        <div class="form-group form-group--currency">
          <label>Moneda <span class="required">*</span></label>
          <select v-model="form.payment_currency">
            <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
          </select>
          <p v-if="getFieldError('payment_currency')" class="field-error">{{ getFieldError('payment_currency') }}</p>
        </div>
      </div>

      <!-- client_name -->
      <div class="form-group">
        <label>Nombre del cliente <span class="required">*</span></label>
        <input
          v-model="form.client_name"
          type="text"
          placeholder="Empresa o persona que contrata"
          :class="{ 'input-error': getFieldError('client_name') }"
        />
        <p v-if="getFieldError('client_name')" class="field-error">{{ getFieldError('client_name') }}</p>
      </div>

      <!-- freelancer_name -->
      <div class="form-group">
        <label>Tu nombre (freelancer) <span class="required">*</span></label>
        <input
          v-model="form.freelancer_name"
          type="text"
          placeholder="Tu nombre completo"
          :class="{ 'input-error': getFieldError('freelancer_name') }"
        />
        <p v-if="getFieldError('freelancer_name')" class="field-error">{{ getFieldError('freelancer_name') }}</p>
      </div>

      <!-- start_date -->
      <div class="form-group">
        <label>Fecha de inicio <span class="required">*</span></label>
        <input
          v-model="form.start_date"
          type="date"
          :class="{ 'input-error': getFieldError('start_date') }"
        />
        <p v-if="getFieldError('start_date')" class="field-error">{{ getFieldError('start_date') }}</p>
      </div>

      <!-- extra_details -->
      <div class="form-group">
        <label>Detalles extra <span class="optional">(opcional)</span></label>
        <textarea
          v-model="form.extra_details"
          rows="4"
          maxlength="2000"
          placeholder="Cualquier información adicional para el contrato..."
        ></textarea>
        <span class="char-count">{{ form.extra_details.length }}/2000</span>
      </div>

      <!-- Error -->
      <p v-if="error" class="form-error">{{ error }}</p>

      <!-- Submit -->
      <button type="submit" class="generate-btn" :disabled="submitting">
        <i v-if="submitting" class="pi pi-spin pi-spinner"></i>
        <span>{{ submitting ? 'Generando contrato...' : 'Generar contrato con IA' }}</span>
      </button>
    </form>
  </div>
</template>

<style scoped>
.generate {
  max-width: 560px;
  margin: 0 auto;
}

.generate-header {
  margin-bottom: 1.5rem;
}

.generate-back {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .5rem .75rem;
  border-radius: .5rem;
  color: var(--body);
  font-size: .875rem;
  transition: all .15s;
}

.generate-back:hover {
  background: var(--hover);
  color: var(--heading);
}

.generate-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--heading);
  margin-bottom: .5rem;
}

.generate-subtitle {
  font-size: .9375rem;
  color: var(--body);
  margin-bottom: 2rem;
}

.generate-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: .875rem;
  font-weight: 600;
  color: var(--heading);
  margin-bottom: .375rem;
}

.required {
  color: var(--danger);
}

.optional {
  font-weight: 400;
  color: var(--body);
  font-size: .8125rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: .75rem .875rem;
  border: 1px solid var(--border);
  border-radius: .75rem;
  background: var(--surface);
  color: var(--heading);
  font-size: .9375rem;
  outline: none;
  transition: border-color .15s, box-shadow .15s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(255, 203, 116, .2);
}

.form-group textarea {
  resize: vertical;
  min-height: 90px;
}

.input-error {
  border-color: var(--danger) !important;
}

.field-error {
  font-size: .8125rem;
  color: var(--danger);
  margin-top: .25rem;
}

.char-count {
  font-size: .75rem;
  color: var(--body);
  text-align: right;
  margin-top: .25rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 110px;
  gap: .875rem;
}

.form-error {
  font-size: .875rem;
  color: var(--danger);
  padding: .75rem 1rem;
  background: var(--danger-bg);
  border-radius: .5rem;
}

.generate-btn {
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

.generate-btn:hover:not(:disabled) {
  background: var(--accent-hover);
}

.generate-btn:disabled {
  opacity: .7;
  cursor: not-allowed;
}
</style>
