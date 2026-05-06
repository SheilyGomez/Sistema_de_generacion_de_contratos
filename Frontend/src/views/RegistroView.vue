<script setup>
import { ref } from 'vue';
import AuthService from '@/services/AuthService';
import { useRouter } from 'vue-router'; 
import { useAuthStore } from '@/stores/auth'; 

const authStore = useAuthStore(); // Ahora sí funcionará
const router = useRouter();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'freelancer'
});

const handleRegister = async () => {
    try {
        const response = await AuthService.register(form.value);
        
        // Guardamos el token 
        if (response.data.token) {
            authStore.setToken(response.data.token);
        }
        
        console.log('Usuario creado:', response.data);
        alert('¡Cuenta creada con éxito!');
        router.push('/login'); 
    } catch (error) {
        console.error('Error:', error.response?.data);
        alert('Error al registrar: ' + (error.response?.data?.message || 'Revisa los datos'));
    }
};
</script>

<template>
  <div class="register-container">
    <h2>Registro de Usuario</h2>
    <form @submit.prevent="handleRegister">
      <input v-model="form.name" type="text" placeholder="Nombre" required>
      <br><br>
      <input v-model="form.email" type="email" placeholder="Correo" required>
      <br><br>
      <input v-model="form.password" type="password" placeholder="Contraseña" required>
      <br><br>
      <input v-model="form.password_confirmation" type="password" placeholder="Confirmar Contraseña" required>
      <br><br>
      
      <label>Tipo de usuario:</label>
      <select v-model="form.role">
        <option value="freelancer">Freelancer</option>
        <option value="lawyer">Abogado</option>
      </select>
      <br><br>

      <button type="submit">Crear Cuenta</button>
    </form>
  </div>
</template>

<style scoped>
/* Agrega un poco de estilo para que no se vea todo pegado */
input, select {
    margin-bottom: 10px;
    padding: 8px;
    width: 100%;
    max-width: 300px;
}
button {
    padding: 10px 20px;
    background-color: #42b983;
    color: white;
    border: none;
    cursor: pointer;
}
</style>