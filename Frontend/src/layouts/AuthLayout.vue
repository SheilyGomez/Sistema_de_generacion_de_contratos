<script setup>
import { ref, onMounted, computed } from 'vue'
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

const mainClass = computed(() => {
  if (sidebarOpen.value) return 'md:ml-64'
  return 'ml-0'
})

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
  <div class="min-h-screen bg-neutral-secondary-soft">
    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-black/50 md:hidden"
      @click="closeSidebar"
    ></div>

    <!-- Sidebar -->
    <Sidebar v-model="sidebarOpen" />

    <!-- Main -->
    <div :class="[mainClass, 'min-h-screen transition-[margin]']">
      <!-- Mobile header -->
      <div class="sticky top-0 z-20 md:hidden bg-neutral-primary-soft border-b border-default px-4 py-3">
        <div class="flex items-center justify-between">
          <button
            @click="toggleSidebar"
            class="p-2 rounded-lg hover:bg-neutral-tertiary text-body"
          >
            <i class="pi pi-bars text-xl"></i>
          </button>
          <span class="text-sm font-semibold text-heading">{{ auth.user?.name }}</span>
        </div>
      </div>

      <!-- Page content -->
      <main class="p-4 md:p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>
