<script setup>
import { ref } from "vue"
import LoginPage from "./components/LogIn.vue"
import Map from './components/Map.vue'
import ControlPanel from './components/ControlPanel/ControlPanel.vue'

const loggedIn = ref(false)

</script>

<template>
    <div id="app">
        <transition name="fade" mode="out-in">
            <LoginPage v-if="!loggedIn" @loggedIn="loggedIn = true" />
            <div v-else class="main-app">
                <ControlPanel @logout="loggedIn = false" />
                <Map />
            </div>
        </transition>
    </div>
</template>

<style>
#app {
    justify-content: center;
    align-items: center;
    height: 100%;
    width: 100%;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
