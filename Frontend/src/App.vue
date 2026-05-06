<script setup>
  import { RouterView, RouterLink } from 'vue-router'
</script>

<template>
  <header>
    <nav>
      <RouterLink to="/register">Registro</RouterLink>
      
      <!-- Nuevos componentes: -->
      <!-- <RouterLink to="/login">Login</RouterLink> -->
    </nav>
  </header>

  <main>
    <RouterView />
  </main>
</template>

<style scoped>
  nav {
    padding: 1rem;
    background-color: #f8f9fa;
    border-bottom: 1px solid #ddd;
    margin-bottom: 2rem;
  }

  nav a {
    margin-right: 15px;
    text-decoration: none;
    color: #42b983;
    font-weight: bold;
  }

  nav a:hover {
    text-decoration: underline;
  }
</style>
