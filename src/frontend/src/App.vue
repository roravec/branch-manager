<script setup>
import { ref, onMounted} from "vue"
import LoginPage from "./components/LogIn.vue"
import Map from './components/Map.vue'
import ControlPanel from './components/ControlPanel/ControlPanel.vue'
import { useUserStore } from '@/stores/user'
import { useUsersStore } from "@/stores/users"
import { useBranchStore } from '@/stores/branches'
import api from '@/api.js'

const userStore = useUserStore();
userStore.restore();

const branchStore = useBranchStore();
const usersStore = useUsersStore();

const loggedIn = ref(!!sessionStorage.getItem('access_token'))

async function handleLogin({ client_id, access_token, refresh_token }) {
    if (access_token && client_id) {
        userStore.setAccessToken(access_token)
        userStore.setRefreshToken(refresh_token)

        try {
            const res = await api.get(`/user/${client_id}`)
            userStore.setUser({ ...res.data, access_token, refresh_token })
        } catch (err) {
            console.error('Nepodarilo sa načítať údaje používateľa', err)
            loggedIn.value = false
            userStore.clear()
            return
        }
    } else {
        userStore.setUser({
            client_id: null,
            access_token: null,
            refresh_token: null,
            name: 'Hosť',
            rights: 0,
            status: null,
            type: null
        })
    }

    usersStore.loadUsers();
    loggedIn.value = true;
}

async function logout() {
    if (!userStore.access_token) {
        loggedIn.value = false;
        return;
    }

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

onMounted(() => {
  branchStore.loadBranches();
})

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
