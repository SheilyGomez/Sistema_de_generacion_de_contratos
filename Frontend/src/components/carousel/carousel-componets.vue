<script setup>

import { ref, onMounted, onBeforeUnmount } from 'vue';

const currentSlide = ref(0);

const images = [

    new URL('../../assets/img/background.jpg', import.meta.url).href,

    new URL('../../assets/img/background-2.jpg', import.meta.url).href,

    new URL('../../assets/img/background-3.jpg', import.meta.url).href

];

let interval = null;

onMounted(() => {

    interval = setInterval(() => {

        currentSlide.value++;

        if (currentSlide.value >= images.length) {

            currentSlide.value = 0;

        }

    }, 4000);

});

onBeforeUnmount(() => {

    clearInterval(interval);

});

</script>

<template>

    <div class="left-panel">
        <div class="carousel">

            <div v-for="(image, index) in images" :key="index" class="slide"
                :class="{ active: currentSlide === index }">

                <img :src="image" alt="Slide">

            </div>

        </div>
    </div>


</template>

<style scoped>
/* panel izquierdo */

/* PANEL IZQUIERDO */

.left-panel {

    width: 40%;

    height: 80vh;

    position: relative;

    overflow: hidden;

    border-radius:
        25px;
    
}

/* SLIDES */

.slide {

    position: absolute;

    inset: 0;

    width: 100%;

    height: 100%;

    opacity: 0;

    transition: opacity 1s ease;
}

/* SLIDE ACTIVO */

.slide.active {

    opacity: 1;

    z-index: 1;
}

/* IMAGEN */

.slide img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;
}

/* carousel */

.carousel {

    width: 100%;

    height: 100%;

    position: relative;
}


</style>