<script setup>
import { ref } from "vue"
import LoginPage from "./components/LogIn.vue"
import Map from './components/Map.vue'
import ControlPanel from './components/ControlPanel/ControlPanel.vue'

const loggedIn = ref(!!sessionStorage.getItem('access_token'))

function handleLogin({ access_token, refresh_token }) {
  sessionStorage.setItem('access_token', access_token)
  sessionStorage.setItem('refresh_token', refresh_token)
  loggedIn.value = true
}

function logout() {
  sessionStorage.removeItem('access_token')
  sessionStorage.removeItem('refresh_token')
  loggedIn.value = false
}
</script>

<template>
  <div id="app">
    <transition name="fade" mode="out-in">
      <LoginPage v-if="!loggedIn" @loggedIn="handleLogin" />
      <div v-else class="main-app">
        <ControlPanel @logout="logout" />
        <Map />
      </div>
    </transition>
  </div>
</template>

<style>
#app {
  display: flex;
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
