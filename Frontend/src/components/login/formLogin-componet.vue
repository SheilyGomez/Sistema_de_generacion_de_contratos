<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';


import AuthService from '@/services/AuthService';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const loading = ref(false);
const errorMessage = ref('');
const alertType = ref('');

const form = reactive({
    email: '',
    password: ''
});

const login = async () => {

    errorMessage.value = '';
    alertType.value = '';

    if (!form.email || !form.password) {

        errorMessage.value =
            'Todos los campos son obligatorios';

        alertType.value = 'warning';

        return;
    }

    loading.value = true;

    try {

        const response =
            await AuthService.login(form);

        const token = response.data.token;
        const user = response.data.user;

        authStore.setAuth(token, user);

        setTimeout(() => {

            router.push('/dashboard');

        }, 1000);

    } catch (error) {

        console.log(error);

        if (error.response) {

            errorMessage.value =
                error.response.data.message ||
                'Credenciales incorrectas';

            alertType.value = 'error';

        } else {

            errorMessage.value =
                'No se pudo conectar con el servidor';

            alertType.value = 'info';
        }

    } finally {

        loading.value = false;
    }
};
</script>

<template>
    <div class="right-panel">

        <div class="form-container">

            <!-- TITULO -->

            <div class="form-header">

                <h1>
                    Bienvenido
                </h1>

                <p>
                    Inicia sesión para continuar
                </p>

            </div>

            <!-- FORMULARIO -->

            <form @submit.prevent="login">

                <!-- CORREO -->

                <div class="form-group">

                    <label>
                        Correo electrónico
                    </label>

                    <input type="email" v-model="form.email" placeholder="">

                </div>

                <!-- CONTRASEÑA -->

                <div class="form-group">

                    <label>
                        Contraseña
                    </label>

                    <input type="password" v-model="form.password" placeholder="">

                </div>


                <!-- BOTON -->

                <button type="submit" :disabled="loading">

                    <span v-if="loading">

                        Iniciando sesión...

                    </span>

                    <span v-else>

                        Iniciar sesión

                    </span>

                </button>
                <!-- ERROR -->
                <div class="espaciado"></div>
                <div v-if="errorMessage" class="alert-message" :class="alertType">

                    {{ errorMessage }}

                </div>

            </form>


            <!-- REGISTRO -->

            <div class="register-link">

                <p>

                    ¿No tienes una cuenta?

                    <a href="/register">
                        Regístrate
                    </a>

                </p>

            </div>

        </div>

    </div>
</template>

<style>
/* PANEL DERECHO */

.right-panel {

    width: 40%;

    height: 80vh;

    background: white;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 3rem;

    border-radius: 25px;

    box-shadow:
        0 10px 25px rgba(0, 0, 0, 0.1);

    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
}

/* HOVER */

.right-panel:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 20px 40px rgba(0, 0, 0, 0.15);
}

/* ESTILO DEL FORMULARIO */
.form-container {

    width: 100%;

    max-width: 380px;
}

/* HEADER */

.form-header {

    margin-bottom: 2rem;

    text-align: center;
}

.form-header h1 {

    font-size: 2.2rem;

    color: #111827;

    margin-bottom: 10px;
}

.form-header p {

    color: #6b7280;

    font-size: 15px;
}

/* FORMULARIO */

.form-group {

    margin-bottom: 1.5rem;
}

.form-group label {

    display: block;

    margin-bottom: 10px;

    font-weight: 600;

    color: #374151;
}

.form-group input {

    width: 100%;

    padding: 14px;

    border: 1px solid #d1d5db;

    border-radius: 12px;

    outline: none;

    transition: 0.3s;

    font-size: 15px;
}

.form-group input:focus {

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px rgba(37, 99, 235, 0.15);
}

/* BOTON */

button {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 12px;

    background: #2563eb;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;
}

button:hover {

    background: #1d4ed8;
}

/* REGISTRO */

.register-link {

    margin-top: 1rem;

    text-align: center;

    font-size: 14px;

    color: #6b7280;
}

.register-link a {

    color: #2563eb;

    text-decoration: none;

    font-weight: bold;
}

.register-link a:hover {

    text-decoration: underline;
}

/* ALERTA GENERAL */

.alert-message {

    padding: 14px;

    border-radius: 12px;

    margin-bottom: 1rem;

    font-size: 14px;

    font-weight: 500;
}

/* ERROR */

.alert-message.error {

    background: #fee2e2;

    color: #dc2626;

    border-left: 5px solid #dc2626;
}

/* WARNING */

.alert-message.warning {

    background: #fef3c7;

    color: #d97706;

    border-left: 5px solid #d97706;
}

/* INFO */

.alert-message.info {

    background: #dbeafe;

    color: #2563eb;

    border-left: 5px solid #2563eb;
}

.espaciado {
    height: 5px;
}
</style>