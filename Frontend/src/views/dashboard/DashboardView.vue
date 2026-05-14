<template>
    <div id="app">
        <!--Sidebar with Dimmer -->
        <div class="fixed inset-0 flex z-40">
            <!-- Sidebar -->
            <div class="absolute flex top-0 h-screen z-20"
                :class="[right ? 'right-0 flex-row' : 'left-0 flex-row-reverse']">
                <!--Drawer -->
                <button @click.prevent="toggle()"
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-white border border-gray-200 shadow-sm hover:bg-gray-100 hover:shadow-md transition-all duration-300">

                    <i :class="open ? 'pi pi-times' : 'pi pi-bars'" class="text-gray-700 text-lg"></i>

                </button>

                <!-- Sidebar Content -->
                <div ref="content" class="transition-all duration-700 overflow-hidden flex items-center bg-yellow-200"
                    :class="[open ? 'w-72' : 'w-0']">
                    <div class="w-48 text-center font-bold text-xl">Sidebar</div>
                    <slot></slot>
                </div>
            </div>

            <transition name="fade">
                <!-- Dimmer -->
                <div v-if="dimmer && open" @click="toggle()"
                    class="flex-1 bg-gray-400 bg-opacity-75 active:outline-none z-10" />
            </transition>
        </div>

        <!-- Page Content -->
        <div
            class="absolute inset-1/2 rounded bg-green-500 w-1/2 h-1/2 transform -translate-x-1/2 -translate-y-1/2 flex justify-center items-center text-white">
            Page Content
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            open: false,
            dimmer: true,
            right: false
        };
    },
    methods: {
        toggle() {
            this.open = !this.open;
        }
    }
};
</script>

<style>
html {
    background: #efefef;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 1s ease-out;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>