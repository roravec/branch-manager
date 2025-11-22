<script setup>
import { ref, onMounted } from "vue"
import api from "@/api.js"
import { useUsersStore } from "@/stores/users"

const ROLE_LABELS = {
  0: "Zamestnanec",
  3: "Manažér"
}
const editingRole = ref(null);

const props = defineProps({
  branch: Object
})

const usersStore = useUsersStore();

const branchEmployees = ref([]);
const selectedUser = ref(null);
const loadingUsers = ref(null);

async function loadBranchEmployees() {
  try {
    loadingUsers.value = true;
    const response = await api.get(`/branchHasUsers/${props.branch.id}`)
    branchEmployees.value = response.data.map(item => {
      const user = usersStore.users.find(u => u.id == item.userId);
      return {
        ...item,
        user
      };
    });
    loadingUsers.value = false;
  } catch (err) {
    loadingUsers.value = false;
    console.error("Chyba pri načítaní:", err)
  }
}

async function addEmployee() {
  if (!selectedUser.value) return

  try {
    const formData = new FormData()
    formData.append("branchId", props.branch.id)
    formData.append("userId", selectedUser.value)
    await api.post("/branchHasUser", formData)

    selectedUser.value = null
    await loadBranchEmployees()
  } catch (err) {
    console.error("Chyba pri pridaní:", err)
  }
}

async function updateUserRole(id, newRole) {
  try {
    const formData = new FormData();
    formData.append("branchId", props.branch.id);
    formData.append("userId", id);
    formData.append("userRights", newRole);

    await api.post(`/edit/branchHasUser/${id}`, formData);

    const emp = branchEmployees.value.find(e => e.id === id);
    if (emp) emp.userRights = newRole;
  } catch (err) {
    console.error("Chyba pri zmene role:", err);
  }
}


async function removeEmployee(id) {
  try {
    await api.delete(`/branchHasUser/${id}`);
    await loadBranchEmployees();
  } catch (err) {
    console.error("Chyba pri odstraňovaní:", err);
  }
}


onMounted(async () => {
  await usersStore.loadUsers();
  await loadBranchEmployees();
})
</script>

<template>
  <div v-if="!loadingUsers">
    <table>
      <thead>
        <tr>
          <th>Meno</th>
          <th>Identifikátor</th>
          <th>Rola</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="emp in branchEmployees" :key="emp.id">
          <td>{{ emp.user.name }}</td>
          <td>{{ emp.user.identifier }}</td>
          <td>
            <select v-model="emp.userRights" @change="updateUserRole(emp.id, emp.userRights)">
              <option v-for="(label, val) in ROLE_LABELS" :value="val" :key="val">
                {{ label }}
              </option>
            </select>
          </td>
          <td>
            <button @click="removeEmployee(emp.id)" class="btn">Odstrániť</button>
          </td>
        </tr>
      </tbody>
    </table>

    <h4>Pridať zamestnanca</h4>

    <select v-model="selectedUser" class="rightSpacer">
      <option disabled value="">-- vyber používateľa --</option>

      <option v-for="user in usersStore.users" :key="user.id" :value="user.id"
        :disabled="branchEmployees.some(e => e.userId === user.id)" class="rightSpacer">
        {{ user.name }} ({{ user.identifier }})
      </option>
    </select>

    <button @click="addEmployee" :disabled="!selectedUser" class="btn">➕ Pridať</button>
  </div>
  <div v-else>
    Načítavam zamestnancov...
  </div>
</template>

<style scoped>
.rightSpacer {
  margin-right: 0.5em;
}
</style>