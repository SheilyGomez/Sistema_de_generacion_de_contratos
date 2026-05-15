<script setup>
import { ref, onMounted } from 'vue'
import Sidebar from '@/components/sidebar/sidebar-componet.vue'
import { useAuthStore } from '@/stores/auth'
import { useWalletStore } from '@/stores/wallet'

const auth = useAuthStore()
const wallet = useWalletStore()

const sidebarOpen = ref(true)

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

function closeSidebar() {
  sidebarOpen.value = false
}

onMounted(async () => {
  if (!auth.user) {
    await auth.fetchUser()
  }
  if (auth.isFreelancer) {
    wallet.fetchBalance()
  }
})
</script>

<template>
  <div class="layout">
    <div
      v-if="sidebarOpen"
      class="layout-overlay"
      @click="closeSidebar"
    ></div>

    <Sidebar v-model="sidebarOpen" />

    <div class="layout-main">
      <div class="layout-mobile-header">
        <div class="layout-mobile-header-inner">
          <button class="layout-mobile-menu-btn" @click="toggleSidebar">
            <i class="pi pi-bars"></i>
          </button>
          <span class="layout-mobile-user">{{ auth.user?.name }}</span>
        </div>
      </div>

      <div class="layout-main-inner">
        <RouterView />
      </div>
    </div>
  </div>
</template>
