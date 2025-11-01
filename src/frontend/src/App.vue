<script setup>
import { ref } from "vue"
import LoginPage from "./components/LogIn.vue"
import Map from './components/Map.vue'
import ControlPanel from './components/ControlPanel/ControlPanel.vue'
import { useUserStore } from '@/stores/user'
import api from '@/api.js'

const userStore = useUserStore();
userStore.restore();

const loggedIn = ref(!!sessionStorage.getItem('access_token'))

async function handleLogin({ client_id, access_token, refresh_token }) {
    userStore.setAccessToken(access_token);
    userStore.setRefreshToken(refresh_token);

    try {
        const res = await api.get(`/user/${client_id}`)
        userStore.setUser({ ...res.data, access_token, refresh_token })
    } catch (err) {
        console.error('Nepodarilo sa načítať údaje používateľa', err)
        loggedIn.value = false
        userStore.clear();
    }

    loggedIn.value = true
}

async function logout() {
    try {
        await api.post(`/logout`);

        userStore.clear();
        userStore.clearTokens();
        loggedIn.value = false;
    } catch (err) {
        console.error(err);
        alert('Chyba pri odhlasovaní!');
    }
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
