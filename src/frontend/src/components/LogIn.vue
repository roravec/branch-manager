<script setup>
import { ref } from "vue"
import axios from "axios"

const username = ref("")
const password = ref("")

const emit = defineEmits(["loggedIn"])

async function login() {
    if (!username.value || !password.value) {
        alert("Zadajte meno a heslo!")
        return
    }

    try {
        const formData = new FormData()
        formData.append("identifier", username.value)
        formData.append("secret", password.value)
        formData.append("storeLogin", 1)

        const response = await axios.post(import.meta.env.VITE_API_BASE + "/login", formData);
        const { client_id, access_token, refresh_token, error } = response.data
        if (error || !access_token) {
            alert("Prihlásenie zlyhalo. Skontrolujte meno a heslo.")
            return
        }

        emit("loggedIn", { client_id, access_token, refresh_token })
    } catch (error) {
        console.error(error)
        alert("Prihlásenie zlyhalo.")
    }
}

function continueAsGuest() {
    emit("loggedIn", { client_id: null, access_token: null, refresh_token: null });
}

</script>


<template>
    <div class="login-page">
        <h1 class="app-title">Branch Manager</h1>
        <div class="login-container">
            <input type="text" placeholder="Meno" v-model="username" />
            <input type="password" placeholder="Heslo" v-model="password" />
            <button class="btn" @click="login">Prihlásiť sa</button>
            <button class="btn"@click="continueAsGuest">Pokračovať bez prihlásenia</button>
        </div>
    </div>
</template>

<style scoped>
.login-page {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    width: 100vw;
}

.app-title {
    font-size: 50px;
    font-weight: bold;
    margin-bottom: 0.5em;
    color: #EEEEEE;
}

.login-container {
    padding: 2em;
    border-radius: 2em;
    display: flex;
    flex-direction: column;
    gap: 15px;
    width: 300px;
}
</style>
