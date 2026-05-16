<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useWalletStore } from '@/stores/wallet'

defineProps({
  modelValue: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const wallet = useWalletStore()

const displayBalance = computed(() => {
  const bal = wallet?.balance
  if (bal === null || bal === undefined) return '0.00'
  const num = Number(bal)
  return Number.isFinite(num) ? num.toFixed(2) : '0.00'
})

const freelancerMenu = [
  { label: 'Inicio', icon: 'pi pi-home', to: '/freelancer/home' },
  { label: 'Contratos verificados', icon: 'pi pi-file-check', to: '/freelancer/verifications' },
  { label: 'Verificaciones con abogado', icon: 'pi pi-user-edit', to: '/freelancer/lawyer-reviews' },
  { label: 'Contratos generados', icon: 'pi pi-file-plus', to: '/freelancer/generations' },
  { label: 'Configuración', icon: 'pi pi-cog', to: '/freelancer/settings' },
]

const abogadoMenu = [
  { label: 'Inicio', icon: 'pi pi-home', to: '/abogado/home' },
  { label: 'Configuración', icon: 'pi pi-cog', to: '/abogado/settings' },
]

const menuItems = computed(() =>
  auth.isFreelancer ? freelancerMenu : abogadoMenu,
)

function isActive(path) {
  return route.path.startsWith(path)
}

function closeSidebar() {
  if (window.innerWidth < 768) {
    emit('update:modelValue', false)
  }
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <aside
    class="sidebar"
    :class="{ 'sidebar--hidden': !modelValue }"
  >
    <!-- Logo -->
    <div class="sidebar-logo">
      <i class="pi pi-file-pdf"></i>
      <span>GenContratos</span>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <ul class="sidebar-nav-list">
        <li v-for="item in menuItems" :key="item.to">
          <RouterLink
            :to="item.to"
            class="sidebar-item"
            :class="{ 'sidebar-item--active': isActive(item.to) }"
            @click="closeSidebar"
          >
            <i :class="item.icon"></i>
            <span>{{ item.label }}</span>
          </RouterLink>
        </li>
      </ul>
    </nav>

    <!-- User info -->
    <div class="sidebar-user">
      <div class="sidebar-user-info">
        <div class="sidebar-avatar">
          <span>{{ auth.user?.name?.charAt(0)?.toUpperCase() }}</span>
        </div>
        <div style="flex:1; min-width:0">
          <p class="sidebar-user-name">{{ auth.user?.name }}</p>
          <p class="sidebar-user-role">{{ auth.user?.role }}</p>
        </div>
      </div>

      <div v-if="auth.isAuthenticated" class="sidebar-balance">
        <span class="sidebar-balance-label">Saldo</span>
        <span class="sidebar-balance-amount">${{ displayBalance }}</span>
      </div>

      <button class="sidebar-logout" @click="handleLogout">
        <i class="pi pi-sign-out"></i>
        <span>Cerrar sesión</span>
      </button>
    </div>
  </aside>
</template>
