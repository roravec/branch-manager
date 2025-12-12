<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api from '@/api.js'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore();
const users = ref([]);
const selectedId = ref('');
const editableUser = ref({});

const registeringNewUser = ref();

const selectedUser = computed(() =>
  users.value.find(u => u.id === selectedId.value)
)

const visibleUsers = computed(() => {
  if (userStore.isAdmin) return users.value;

  return users.value.filter(u => u.id === userStore.id);
})

async function loadUsers() {
  try {
    const response = await api.get("/users");
    users.value = response.data;
    console.log(response.data);

  } catch (err) {
    console.error('Chyba pri načítaní používateľov:', err);
  }
}

watch(selectedUser, (newUser) => {
  if (newUser) {
    editableUser.value = { ...newUser };
  }
}, { immediate: true })

async function saveUser() {
  try {

    const formData = new FormData();
    formData.append('name', editableUser.value.name);
    formData.append('identifier', editableUser.value.identifier);
    formData.append('secret_hash', editableUser.value.secret_hash);
    formData.append('rights', editableUser.value.rights);
    formData.append('status', editableUser.value.status);
    formData.append('type', editableUser.value.type);

    const res = await api.post(`/edit/user/${editableUser.value.id}`, formData)

    const index = users.value.findIndex(u => u.id === editableUser.value.id);
    if (index !== -1) users.value[index] = { ...res.data };
    selectedId.value = res.data.id ;
    alert("Pobočka bol uložený");
  } catch (err) {
    console.error(err);
    alert('Chyba pri ukladaní používateľa!');
  }
}

async function deleteUser() {
  try {
    const confirmDelete = confirm(`Naozaj chcete odstrániť používateľa ${editableUser.value.name}?`);
    if (!confirmDelete) return;

    await api.delete(`/user/${editableUser.value.id}`);

    users.value = users.value.filter(u => u.id !== editableUser.value.id);
    selectedId.value = '';
    editableUser.value = {};
    alert("Pobočka bol odstránený");
  } catch (err) {
    console.error(err);
    alert('Chyba pri vymazavaní používateľa!');
  }
}


async function createUser() {
  try {
    const formData = new FormData();
    formData.append('identifier', editableUser.value.identifier);
    formData.append('name', editableUser.value.name);
    formData.append('secret', editableUser.value.secret);

    const res = await api.post('/user/register', formData);
    users.value.push(res.data);
    selectedId.value = res.data.id;
    registeringNewUser.value = false;

    alert('Používateľ bol úspešne vytvorený.');
  } catch (err) {
    console.error(err);
    alert('Chyba pri vytváraní používateľa!');
  }
}

function startCreating() {
  editableUser.value = {
    identifier: '',
    name: '',
    secret: ''
  };
  registeringNewUser.value = true;
  selectedId.value = '';
}

onMounted(async () => {
  await loadUsers()

  if (!userStore.isAdmin && userStore.id) {
    selectedId.value = userStore.id;
  }

  if (userStore.isAdmin && users.value.length && !selectedId.value) {
    selectedId.value = users.value[0].id;
  }
})

const props = defineProps({
  user: Object
})

</script>

<template>
  <div>
    <h2 v-if="registeringNewUser">Nový používateľ</h2>
    <h2 v-else-if="userStore.isAdmin">Používatelia</h2>
    <h2 v-else>Používateľ</h2>

    <div v-if="!registeringNewUser" class="select-create-row">
      <select v-model="selectedId" class="dropdown" :disabled="!userStore.isAdmin">
        <option disabled value="">-- Vyber používateľa --</option>
        <option v-for="u in visibleUsers" :key="u.id" :value="u.id">
          {{ u.name }} ({{ u.identifier }})
        </option>
      </select>

      <button v-if="userStore.isAdmin" @click="startCreating" class="btn">
        ➕
      </button>
    </div>

    <div v-if="selectedUser || registeringNewUser">
      <table>
        <tbody>
          <tr>
            <th>Používateľské meno</th>
            <td><input v-model="editableUser.identifier" :readonly="!userStore.isAdmin" /></td>
          </tr>
          <tr>
            <th>Meno</th>
            <td><input v-model="editableUser.name" :readonly="!userStore.isAdmin" /></td>
          </tr>
          <tr v-if="registeringNewUser">
            <th>Heslo</th>
            <td><input type="password" v-model="editableUser.secret" /></td>
          </tr>
          <tr v-if="!registeringNewUser">
            <th>Oprávnenia</th>
            <td><input v-model.number="editableUser.rights" inputmode="numeric" type="number"
                :readonly="!userStore.isAdmin" /></td>
          </tr>
          <tr v-if="!registeringNewUser">
            <th>Rola</th>
            <td><input v-model.number="editableUser.type" inputmode="numeric" type="number"
                :readonly="!userStore.isAdmin" /></td>
          </tr>
          <tr v-if="!registeringNewUser">
            <th>Status</th>
            <td>{{ editableUser.status }}</td>
          </tr>
          <tr v-if="!registeringNewUser">
            <th>Naposledy online</th>
            <td>{{ editableUser.last_seen }}</td>
          </tr>
          <tr v-if="!registeringNewUser">
            <th>Vytvorený</th>
            <td>{{ editableUser.created_at }}</td>
          </tr>
        </tbody>
      </table>
      <br>
      <div v-if="userStore.isAdmin">
        <button v-if="registeringNewUser" @click="createUser" class="btn rightSpacer">✅ Vytvoriť používateľa</button>
        <button v-if="registeringNewUser" @click="registeringNewUser = false" class="btn">❌ Zrušiť</button>

        <button v-if="!registeringNewUser" @click="saveUser" class="btn rightSpacer">💾 Uložiť zmeny</button>
        <button v-if="!registeringNewUser && editableUser.id !== userStore.id" @click="deleteUser" class="btn">🗑️
          Vymazať používateľa</button>
      </div>
    </div>

    <div v-else-if="users.length">👆 Vyber používateľa z menu</div>
    <div v-else>Načítavam používateľov...</div>
  </div>
</template>

<style scoped>
.rightSpacer {
  margin-right: 0.5em;
}

.dropdown {
  flex: 1;
}
</style>