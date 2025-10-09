<script setup>
import { ref } from "vue"

const username = ref("")
const password = ref("")

const emit = defineEmits(["loggedIn"])

function login() {
    if (username.value && password.value) {
        emit("loggedIn")
    } else {
        alert("Zadajte meno a heslo!")
    }
}
</script>

<template>
    <div class="login-page">
        <h1 class="app-title">Branch Manager</h1>
        <div class="login-container">
            <input type="text" placeholder="Meno" v-model="username" />
            <input type="password" placeholder="Heslo" v-model="password" />
            <button class="btn" @click="login">Prihlásiť sa</button>
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
