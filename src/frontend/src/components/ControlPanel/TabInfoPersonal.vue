<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const users = ref([])
const selectedId = ref('')
const editableUser = ref({})

const selectedUser = computed(() =>
  users.value.find(u => u.id === selectedId.value)
)

async function loadUsers() {
  try {
    const res = await fetch('/api/users')
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    users.value = await res.json()

    if (users.value.length > 0) {
      selectedId.value = users.value[0].id
    }

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
    const res = await fetch(`/api/user/${editableUser.value.id}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(editableUser.value)
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const data = await res.json()
    console.log('Používateľ uložený:', data)
    alert('Používateľ bol uložený!')
  } catch (err) {
    console.error('Chyba pri ukladaní:', err)
    alert('Chyba pri ukladaní používateľa!')
  }
}

onMounted(loadUsers)

const props = defineProps({
  user: Object
})

</script>

<template>
  <div>
    <h2>Používateľ</h2>

    <select v-model="selectedId" class="dropdown">
      <option disabled value="">-- Vyber používateľa --</option>
      <option v-for="u in users" :key="u.id" :value="u.id">
        {{ u.name }} ({{ u.identifier }})
      </option>
    </select>

    <div v-if="selectedUser">
      <table class="user-table">
        <tr>
          <th>Používateľské meno</th>
          <td><input v-model="editableUser.identifier" /></td>
        </tr>
        <tr>
          <th>Meno</th>
          <td><input v-model="editableUser.name" /></td>
        </tr>
        <tr>
          <th>Oprávnenia</th>
          <td><input v-model.number="editableUser.rights" inputmode="numeric" type="number"/></td>
        </tr>
        <tr>
          <th>Status</th>
          <td><input v-model.number="editableUser.status" inputmode="numeric" type="number"/></td>
        </tr>
        <tr>
          <th>Rola</th>
          <td><input v-model.number="editableUser.type" inputmode="numeric" type="number"/></td>
        </tr>
        <tr>
          <th>Naposledy online</th>
          <td>{{ editableUser.last_seen }}</td>
        </tr>
        <tr>
          <th>Vytvorený</th>
          <td>{{ editableUser.created_at }}</td>
        </tr>
      </table>
      <br>
      <button @click="saveUser" class="btn">💾 Uložiť zmeny</button>
    </div>

    <div v-else-if="users.length">👆 Vyber používateľa z menu</div>
    <div v-else>Načítavam používateľov...</div>
  </div>
</template>