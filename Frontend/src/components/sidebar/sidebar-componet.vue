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

const isActive = (path) => route.path.startsWith(path)

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
    class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform bg-neutral-primary-soft border-e border-default"
    :class="modelValue ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
  >
    <div class="flex flex-col h-full">
      <!-- Logo -->
      <div class="flex items-center h-16 px-5 border-b border-default">
        <i class="pi pi-file-pdf text-2xl text-fg-brand"></i>
        <span class="ms-3 text-lg font-semibold text-heading whitespace-nowrap">GenContratos</span>
      </div>

      <!-- Menu -->
      <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-1">
          <li v-for="item in menuItems" :key="item.to">
            <RouterLink
              :to="item.to"
              @click="closeSidebar"
              class="flex items-center px-3 py-2.5 rounded-lg transition-colors"
              :class="
                isActive(item.to)
                  ? 'bg-neutral-tertiary text-fg-brand font-medium'
                  : 'text-body hover:bg-neutral-tertiary hover:text-heading'
              "
            >
              <i :class="[item.icon, 'text-lg w-5 text-center']"></i>
              <span class="ms-3 text-sm">{{ item.label }}</span>
            </RouterLink>
          </li>
        </ul>
      </nav>

      <!-- User info -->
      <div class="border-t border-default p-4">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-9 h-9 rounded-full bg-neutral-tertiary flex items-center justify-center">
            <span class="text-sm font-medium text-heading">
              {{ auth.user?.name?.charAt(0)?.toUpperCase() }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-heading truncate">{{ auth.user?.name }}</p>
            <p class="text-xs text-body capitalize">{{ auth.user?.role }}</p>
          </div>
        </div>

        <div
          v-if="auth.isFreelancer"
          class="flex items-center justify-between px-2 py-1.5 mb-3 bg-neutral-secondary-soft rounded-lg"
        >
          <span class="text-xs text-body">Saldo</span>
          <span class="text-sm font-semibold text-heading">${{ (Number(wallet.balance) || 0).toFixed(2) }}</span>
        </div>

        <button
          @click="handleLogout"
          class="flex items-center w-full px-3 py-2 text-sm text-body rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors"
        >
          <i class="pi pi-sign-out text-lg w-5 text-center"></i>
          <span class="ms-3">Cerrar sesión</span>
        </button>
      </div>
    </div>
  </aside>
</template>
