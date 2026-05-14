<script setup>

import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';


import AuthService from '@/services/AuthService';
import { useAuthStore } from '@/stores/auth';
import carousel from '../../components/carousel/carousel-componets.vue';
import registerForm from '../../components/register/formRegister-componet.vue';

const router = useRouter();
const authStore = useAuthStore();

const loading = ref(false);
const errorMessage = ref('');
const alertType = ref('');


</script>

    <template>

        <div class="container">
            <registerForm />
            <carousel />
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
    /* este componete permite el cambio de posicion de los formularios depende de como se escriba */

    justify-content: center;

    align-items: center;

    gap: 20px;

    padding: 40px;

    background: #ffffff;

    overflow: hidden;
}
</style>
