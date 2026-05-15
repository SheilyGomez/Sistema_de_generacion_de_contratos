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

    name: '',

    email: '',

    password: '',

    password_confirmation: '',

    role: ''

});
const register = async () => {

    errorMessage.value = '';

    alertType.value = '';

    /* VALIDAR CAMPOS */

    if (
        !form.name ||
        !form.email ||
        !form.password ||
        !form.password_confirmation ||
        !form.role
    ) {

        errorMessage.value =
            'Todos los campos son obligatorios';

        alertType.value = 'warning';

        return;
    }

    /* VALIDAR PASSWORDS */

    if (
        form.password !==
        form.password_confirmation
    ) {

        errorMessage.value =
            'Las contraseñas no coinciden';

        alertType.value = 'warning';

        return;
    }

    /* VALIDAR LONGITUD */

    if (form.password.length < 8) {

        errorMessage.value =
            'La contraseña debe tener mínimo 8 caracteres';

        alertType.value = 'warning';

        return;
    }

    loading.value = true;

    try {

        const response =
            await AuthService.register(form);

        console.log(response.data);

        /* RESPUESTA */

        const token = response.data.token;

        const user = response.data.user;

        /* GUARDAR SESION */

        authStore.setAuth(token, user);

        /* MENSAJE */

        errorMessage.value =
            'Cuenta creada correctamente';

        alertType.value = 'success';

        /* LIMPIAR */
        form.name = '';

        form.email = '';

        form.password = '';

        form.password_confirmation = '';

        form.role = '';

        /* REDIRECCION */

        setTimeout(() => {

            router.push('/login');

        }, 1500);

    } catch (error) {

        console.log(error);

        /* ERROR LARAVEL */

        if (error.response) {

            if (error.response.status === 422) {

                const errors =
                    error.response.data.errors;

                errorMessage.value =
                    Object.values(errors)
                        .flat()
                        .join(' ');

            } else {

                errorMessage.value =
                    error.response.data.message ||
                    'Error al registrar usuario';

            }

            alertType.value = 'error';

        } else {

            errorMessage.value =
                'No se pudo conectar con el servidor';

            alertType.value = 'info';
        }

    } finally {

        loading.value = false;

    }
}
</script>

<template>
    <div class="right-panel">

        <div class="form-container">

            <!-- TITULO -->

            <div class="form-header">

                <h1>
                    Registrate
                </h1>

                <p>
                    Registrate para continuar
                </p>

            </div>

            <!-- FORMULARIO -->

            <form @submit.prevent="register">

                <!-- NOMBRE -->

                <div class="form-group">

                    <label>
                        Nombre completo
                    </label>

                    <input type="text" v-model="form.name" placeholder="">

                </div>

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

                <div class="form-group">

                    <label>
                        Ingresa nuevamente tu contraseña
                    </label>

                    <input type="password" v-model="form.password_confirmation" placeholder="">

                </div>

                <!-- ROL -->

                <div class="form-group">

                    <label>
                        Rol
                    </label>

                    <select v-model="form.role">

                        <option disabled value="">
                            Selecciona un rol
                        </option>

                        <option value="freelancer">
                            Freelancer
                        </option>

                        <option value="abogado">
                            Abogado
                        </option>

                    </select>

                </div>

                <!-- BOTON -->

                <button type="submit" :disabled="loading">

                    <span v-if="loading">

                        Registrando...

                    </span>

                    <span v-else>

                        Registrarse

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

                    ¿Ya tienes una cuenta?

                    <a href="/login">
                        Inicia sesión
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

    align-items: flex-start;

    padding: 2rem;

    border-radius: 25px;

    box-shadow:
        0 10px 25px rgba(0, 0, 0, 0.1);

    overflow-y: auto;
}

/* QUITAR EL SCROLL */

.right-panel::-webkit-scrollbar {

    width: 6px;
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

    margin-bottom: 20px;

    text-align: center;
}

.form-header h1 {

    font-size: 1.8rem;

    margin-bottom: 5px;
}

.form-header p {
    margin-top: 1px;

    color: var(--body);

    font-size: 15px;
}

/* FORMULARIO */

.form-group {

    margin-bottom: 12px;
}

.form-group label {

    display: block;

    margin-bottom: 4px;

    font-weight: 600;

    font-size: 14px;

    color: var(--heading);
}

.form-group input,
.form-group select {

    width: 100%;

    padding: 10px 12px;

    border: 1px solid var(--border);

    border-radius: 10px;

    outline: none;

    transition: 0.3s;

    font-size: 14px;

    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {

    border-color: var(--accent);

    box-shadow:
        0 0 0 3px rgba(255, 203, 116, 0.2);
}

/* BOTON */

button {

    margin-top: 10px;

    width: 100%;

    padding: 12px;

    border: none;

    border-radius: 12px;

    background: var(--accent);

    color: var(--accent-text);

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;
}

button:hover {

    background: var(--accent-hover);
}

/* REGISTRO */

.register-link {

    margin-top: 1px;

    text-align: center;

    font-size: 14px;

    color: #6b7280;
}

.register-link a {

    color: var(--accent);

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

    background: rgba(255, 203, 116, 0.12);

    color: #B8860B;

    border-left: 5px solid var(--accent);
}

.espaciado {
    height: 5px;
}
</style>