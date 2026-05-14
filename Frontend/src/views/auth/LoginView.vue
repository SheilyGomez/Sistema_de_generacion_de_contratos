<script setup>

import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';


import AuthService from '@/services/AuthService';
import { useAuthStore } from '@/stores/auth';
import carousel from '../../components/carousel/carousel-componets.vue';
import loginform from '../../components/login/formLogin-componet.vue';

const router = useRouter();
const authStore = useAuthStore();

</script>

<template>

    <div class="container">
        <carousel />

        <loginform />
    </div>

</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

* {

    font-family: 'Poppins', sans-serif;

    margin: 0;

    padding: 0;

    box-sizing: border-box;
}

.container {

    width: 100%;

    height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 20px;

    padding: 40px;

    background: #ffffff;

    overflow: hidden;
}
</style>
