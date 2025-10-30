<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api from '@/api.js'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
const users = ref([])
const selectedId = ref('')
const editableUser = ref({})

const selectedUser = computed(() =>
  users.value.find(u => u.id === selectedId.value)
)

const visibleUsers = computed(() => {
  if (userStore.isAdmin) return users.value

  return users.value.filter(u => u.id === userStore.id)
})

async function loadUsers() {
  try {
    const response = await api.get("/users");
    users.value = response.data
    console.log(response.data)

  } catch (err) {
    console.error('Chyba pri načítaní používateľov:', err)
  }
}

watch(selectedUser, (newUser) => {
  if (newUser) {
    editableUser.value = { ...newUser }
  }
}, { immediate: true })

async function saveUser() {
  try {

    const payload = {
      name: editableUser.value.name,
      identifier: editableUser.value.identifier,
      secret_hash: editableUser.value.secret_hash,
      rights: editableUser.value.rights,
      status: editableUser.value.status,
      type: editableUser.value.type
    }

    const res = await api.post(`/edit/user/${editableUser.value.id}`, payload)

    const index = users.value.findIndex(u => u.id === editableUser.value.id)
    if (index !== -1) users.value[index] = { ...res.data }

  } catch (err) {
    console.error(err);
    alert('Chyba pri ukladaní používateľa!')
  }
}

onMounted(async () => {
  await loadUsers()

  if (!userStore.isAdmin && userStore.id) {
    selectedId.value = userStore.id
  }

  if (userStore.isAdmin && users.value.length && !selectedId.value) {
    selectedId.value = users.value[0].id
  }
})

const props = defineProps({
  user: Object
})

</script>

<template>
  <div>
    <h2>Používateľ</h2>

    <select v-model="selectedId" class="dropdown" :disabled="!userStore.isAdmin">
      <option disabled value="">-- Vyber používateľa --</option>
      <option v-for="u in visibleUsers" :key="u.id" :value="u.id">
        {{ u.name }} ({{ u.identifier }})
      </option>
    </select>

    <div v-if="selectedUser">
      <table class="user-table">
        <tr>
          <th>Používateľské meno</th>
          <td><input v-model="editableUser.identifier" :readonly="!userStore.isAdmin" /></td>
        </tr>
        <tr>
          <th>Meno</th>
          <td><input v-model="editableUser.name" :readonly="!userStore.isAdmin" /></td>
        </tr>
        <tr>
          <th>Oprávnenia</th>
          <td><input v-model.number="editableUser.rights" inputmode="numeric" type="number"
              :readonly="!userStore.isAdmin" /></td>
        </tr>
        <tr>
          <th>Rola</th>
          <td><input v-model.number="editableUser.type" inputmode="numeric" type="number"
              :readonly="!userStore.isAdmin" /></td>
        </tr>
        <tr>
          <th>Naposledy online</th>
          <td>{{ editableUser.last_seen }}</td>
        </tr>
        <tr>
          <th>Vytvorený</th>
          <td>{{ editableUser.created_at }}</td>
        </tr>
        <tr>
          <th>Status</th>
          <td>{{ editableUser.status }}</td>
        </tr>
      </table>
      <br>
      <button @click="saveUser" class="btn" v-if="userStore.isAdmin">💾 Uložiť zmeny</button>
    </div>

    <div v-else-if="users.length">👆 Vyber používateľa z menu</div>
    <div v-else>Načítavam používateľov...</div>
  </div>
</template>