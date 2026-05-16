<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<template>
  <div class="home-freelancer">
    <h1 class="home-title">¿Qué quieres hacer hoy?</h1>

    <div class="home-cards">
      <!-- Verificar contrato -->
      <button class="home-card" @click="router.push('/freelancer/verify')">
        <div class="home-card-icon">
          <i class="pi pi-shield"></i>
        </div>
        <h2 class="home-card-title">Verificar contrato</h2>
        <p class="home-card-desc">
          Sube un PDF y la IA lo analizará en busca de cláusulas maliciosas, riesgos legales
          y condiciones desfavorables.
        </p>
        <span class="home-card-action">Verificar</span>
      </button>

      <!-- Crear contrato -->
      <button class="home-card" @click="router.push('/freelancer/generate')">
        <div class="home-card-icon">
          <i class="pi pi-file-plus"></i>
        </div>
        <h2 class="home-card-title">Crear contrato nuevo</h2>
        <p class="home-card-desc">
          Define los requerimientos y la IA generará un contrato profesional listo para
          descargar y usar.
        </p>
        <span class="home-card-action">Crear</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.home-freelancer {
  max-width: 800px;
  margin: 0 auto;
}

.home-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--heading);
  margin-bottom: 2rem;
  text-align: center;
}

.home-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.home-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 2rem 1.5rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1rem;
  cursor: pointer;
  transition: all .2s ease;
}

.home-card:hover {
  border-color: var(--accent);
  box-shadow: 0 4px 20px rgba(255, 203, 116, .12);
  transform: translateY(-2px);
}

.home-card-icon {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 1rem;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.25rem;
}

.home-card-icon i {
  font-size: 1.75rem;
  color: var(--accent);
}

.home-card-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--heading);
  margin-bottom: .75rem;
}

.home-card-desc {
  font-size: .875rem;
  color: var(--body);
  line-height: 1.6;
  margin-bottom: 1.5rem;
  flex: 1;
}

.home-card-action {
  display: inline-block;
  padding: .625rem 2rem;
  background: var(--accent);
  color: var(--accent-text);
  font-size: .9375rem;
  font-weight: 600;
  border-radius: .75rem;
  transition: background .15s;
}

.home-card-action:hover {
  background: var(--accent-hover);
}

@media (max-width: 600px) {
  .home-cards {
    grid-template-columns: 1fr;
  }
}
</style>
